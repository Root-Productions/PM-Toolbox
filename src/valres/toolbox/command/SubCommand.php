<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use pocketmine\command\CommandSender;
use ReflectionClass;
use Throwable;
use valres\toolbox\command\argument\ArgumentTrait;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\result\CommandFailure;
use valres\toolbox\command\result\CommandSuccess;
use valres\toolbox\command\rules\PermissionRule;
use valres\toolbox\command\rules\RuleTrait;

abstract class SubCommand {
    use ArgumentTrait;
    use RuleTrait;

    /** @var string[] */
    private array $aliases;

    /** @var array<SubCommand> */
    private array $subCommands = [];

    private ?Command $command = null;

    /** @var string[] */
    private array $permissionParents = [];

    private ?string $permission = null;

    /**
     * Creates a sub-command definition.
     *
     * @param  string   $name
     * @param  string   $description
     * @param  string[] $aliases
     */
    public function __construct(
        private readonly string $name,
        private readonly string $description = "",
        array $aliases = []
    ) {
        $this->aliases = array_map('strtolower', $aliases);
        $this->configure();
    }

    /**
     * Configures arguments, rules and nested sub-commands.
     *
     * @return void
     */
    protected function configure(): void {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Returns aliases accepted for this sub-command.
     *
     * @return string[]
     */
    public function getAliases(): array {
        return $this->aliases;
    }

    /**
     * Adds a nested sub-command and rejects duplicate names or aliases.
     *
     * @param  SubCommand $subCommand
     *
     * @throws CommandConfigurationException
     * @return $this
     */
    public function addSubCommand(SubCommand $subCommand): self {
        foreach ([$subCommand->getName(), ...$subCommand->getAliases()] as $name) {
            if ($this->getSubCommand($name) !== null) {
                throw new CommandConfigurationException("Duplicate sub-command or alias '{$name}'");
            }
        }

        $this->subCommands[] = $subCommand;
        if ($this->command !== null) {
            $subCommand->bindTo($this->command, [...$this->permissionParents, $this->name]);
        }
        return $this;
    }

    /**
     * Finds a nested sub-command by name or alias.
     *
     * @param  string $name
     *
     * @return SubCommand|null
     */
    public function getSubCommand(string $name): ?SubCommand {
        foreach ($this->subCommands as $subCommand) {
            if ($subCommand->matches($name)) {
                return $subCommand;
            }
        }

        return null;
    }

    /**
     * Returns nested sub-commands registered on this sub-command.
     *
     * @return SubCommand[]
     */
    public function getSubCommands(): array {
        return $this->subCommands;
    }

    /**
     * Checks whether a raw command token matches the name or any alias.
     *
     * @param  string $name
     *
     * @return bool
     */
    public function matches(string $name): bool {
        $name = strtolower($name);
        return strtolower($this->name) === $name || in_array($name, $this->aliases, true);
    }

    /**
     * Validates, parses and executes this sub-command.
     *
     * @param  Command       $command
     * @param  CommandSender $sender
     * @param  string        $label
     * @param  string[]      $rawArgs
     *
     * @return CommandFailure|CommandSuccess
     */
    public function tryRun(Command $command, CommandSender $sender, string $label, array $rawArgs): CommandFailure|CommandSuccess {
        $ruleResult = $this->testRules($sender);
        if (!$ruleResult->isSuccess()) {
            return new CommandFailure($sender, CommandFailure::CONSTRAINT_FAILED, [
                "rules_failed" => $ruleResult->getFailed()
            ]);
        }

        if (!empty($rawArgs)) {
            $subCommand = $this->getSubCommand((string) $rawArgs[0]);
            if ($subCommand !== null) {
                return $subCommand->tryRun($command, $sender, $label . " " . $rawArgs[0], array_slice($rawArgs, 1));
            }
        }

        $validation = $this->validateArguments($sender, $rawArgs);
        if (!$validation["valid"]) {
            return new CommandFailure($sender, $validation["type"], [
                ...$validation,
                "usage" => $this->getUsageLine($label)
            ]);
        }

        $arguments = $this->parseArguments($sender, $rawArgs);
        $context = new CommandContext($sender, $arguments, $label, $rawArgs, $command, $this);
        try {
            $returnValue = $this->onRun($context);
            $this->notifyRulesExecuted($sender);
        } catch (Throwable $throwable) {
            return new CommandFailure($sender, CommandFailure::EXECUTION_ERROR, [
                "message" => $throwable->getMessage(),
                "exception" => $throwable
            ]);
        }

        return new CommandSuccess($sender, $arguments, $label, $returnValue);
    }

    /**
     * Runs the sub-command after rules and argument validation have passed.
     *
     * @param  CommandContext $context
     *
     * @return mixed
     */
    abstract protected function onRun(CommandContext $context): mixed;

    /**
     * Binds this sub-command to its root command and resolves inherited permission paths.
     *
     * @param  Command  $command
     * @param  string[] $parents
     *
     * @return void
     */
    public function bindTo(Command $command, array $parents = []): void {
        $this->command = $command;
        $this->permissionParents = $parents;

        [$permission, $isOp] = $this->resolvePermission($command, $parents);
        if ($this->permission !== $permission) {
            $this->permission = $permission;
            $command->registerPermission($permission, $isOp, $this->description);
            $this->addRule(new PermissionRule($permission));
        }

        foreach ($this->subCommands as $subCommand) {
            $subCommand->bindTo($command, [...$parents, $this->name]);
        }
    }

    /**
     * Returns the permission node registered for this sub-command.
     *
     * @return string|null
     */
    public function getPermission(): ?string {
        return $this->permission;
    }

    /**
     * Builds usage lines for this sub-command and its nested children.
     *
     * @param  string $parentLabel
     *
     * @return string[]
     */
    public function getUsageLines(string $parentLabel): array {
        $label = $parentLabel . " " . $this->name;
        $lines = [$this->getUsageLine($label)];

        foreach ($this->subCommands as $subCommand) {
            $lines = [...$lines, ...$subCommand->getUsageLines($label)];
        }

        return array_values(array_unique($lines));
    }

    /**
     * Resolves the permission node from attributes or the command hierarchy.
     *
     * @param  Command  $command
     * @param  string[] $parents
     *
     * @return array{0: string, 1: bool}
     */
    private function resolvePermission(Command $command, array $parents): array {
        $permission = implode(".", [
            strtolower($this->name),
            ...array_reverse(array_map('strtolower', $parents)),
            strtolower($command->getName()),
            "command"
        ]);
        $isOp = true;

        $attributes = (new ReflectionClass($this))->getAttributes(CommandPermission::class);
        if ($attributes !== []) {
            /** @var CommandPermission $definition */
            $definition = $attributes[0]->newInstance();
            $permission = $definition->permission ?? $permission;
            $isOp = $definition->isOp;
        }

        return [$permission, $isOp];
    }
}
