<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use pocketmine\command\CommandSender;
use Throwable;
use valres\toolbox\command\argument\ArgumentTrait;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\result\CommandFailure;
use valres\toolbox\command\result\CommandSuccess;
use valres\toolbox\command\rules\RuleTrait;

abstract class SubCommand {
    use ArgumentTrait;
    use RuleTrait;

    /** @var string[] */
    private array $aliases;

    /** @var array<SubCommand> */
    private array $subCommands = [];

    public function __construct(
        private readonly string $name,
        private readonly string $description = "",
        array $aliases = []
    ) {
        $this->aliases = array_map('strtolower', $aliases);
        $this->configure();
    }

    protected function configure(): void {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getAliases(): array {
        return $this->aliases;
    }

    public function addSubCommand(SubCommand $subCommand): self {
        foreach ([$subCommand->getName(), ...$subCommand->getAliases()] as $name) {
            if ($this->getSubCommand($name) !== null) {
                throw new CommandConfigurationException("Duplicate sub-command or alias '{$name}'");
            }
        }

        $this->subCommands[] = $subCommand;
        return $this;
    }

    public function getSubCommand(string $name): ?SubCommand {
        foreach ($this->subCommands as $subCommand) {
            if ($subCommand->matches($name)) {
                return $subCommand;
            }
        }

        return null;
    }

    public function getSubCommands(): array {
        return $this->subCommands;
    }

    public function matches(string $name): bool {
        $name = strtolower($name);
        return strtolower($this->name) === $name || in_array($name, $this->aliases, true);
    }

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
        } catch (Throwable $throwable) {
            return new CommandFailure($sender, CommandFailure::EXECUTION_ERROR, [
                "message" => $throwable->getMessage(),
                "exception" => $throwable
            ]);
        }

        return new CommandSuccess($sender, $arguments, $label, $returnValue);
    }

    abstract protected function onRun(CommandContext $context): mixed;

    /** @return string[] */
    public function getUsageLines(string $parentLabel): array {
        $label = $parentLabel . " " . $this->name;
        $lines = [$this->getUsageLine($label)];

        foreach ($this->subCommands as $subCommand) {
            $lines = [...$lines, ...$subCommand->getUsageLines($label)];
        }

        return array_values(array_unique($lines));
    }
}
