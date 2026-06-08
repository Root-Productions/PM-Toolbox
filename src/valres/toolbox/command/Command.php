<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use pocketmine\command\Command as PMCommand;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use ReflectionClass;
use Throwable;
use valres\toolbox\command\argument\Argument;
use valres\toolbox\command\attribute\CommandArgument;
use valres\toolbox\command\attribute\CommandInfo;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\argument\ArgumentTrait;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\result\CommandFailure;
use valres\toolbox\command\result\CommandSuccess;
use valres\toolbox\command\rules\PermissionRule;
use valres\toolbox\command\rules\RuleTrait;
use valres\toolbox\ToolboxLoader;

abstract class Command extends PMCommand implements PluginOwned {
    use ArgumentTrait;
    use RuleTrait;

    private Plugin $plugin;

    protected CommandSender $sender;

    /** @var array<SubCommand> */
    private array $subCommands = [];

    private string $commandPermission;

    protected ArgumentsList $argumentsList;

    /** @throws CommandConfigurationException */
    public function __construct(?string $name = null, ?string $description = null, array $aliases = []) {
        [$name, $description, $aliases] = $this->resolveMetadata($name, $description, $aliases);

        parent::__construct($name, $description, null, $aliases);

        $this->plugin = ToolboxLoader::getLoader();
        $this->loadAttributePermission();
        $this->loadAttributeArguments();
        $this->configure();
        $this->setUsage(implode("\n", $this->getUsageLines()));
    }

    protected function configure(): void {
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    /** @throws CommandConfigurationException */
    public function addSubCommand(SubCommand $subCommand): self {
        foreach ([$subCommand->getName(), ...$subCommand->getAliases()] as $name) {
            if ($this->getSubCommand($name) !== null) {
                throw new CommandConfigurationException("Duplicate sub-command or alias '{$name}'");
            }
        }

        $this->subCommands[] = $subCommand;
        $subCommand->bindTo($this);
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

    /** @return string[] */
    public function getUsageLines(?string $label = null): array {
        $label ??= $this->getName();
        $lines = [$this->getUsageLine($label)];

        foreach ($this->subCommands as $subCommand) {
            $lines = [...$lines, ...$subCommand->getUsageLines($label)];
        }

        return array_values(array_unique($lines));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        $ruleResult = $this->testRules($sender);
        if (!$ruleResult->isSuccess()) {
            $this->fail(new CommandFailure(
                $sender,
                CommandFailure::CONSTRAINT_FAILED,
                [
                    "rules_failed" => $ruleResult->getFailed()
                ]
            ));
            return;
        }

        if (!empty($args)) {
            $subCommand = $this->getSubCommand((string) $args[0]);
            if ($subCommand !== null) {
                $result = $subCommand->tryRun($this, $sender, $commandLabel . " " . $args[0], array_slice($args, 1));
                if ($result instanceof CommandFailure) {
                    $this->fail($result);
                } else {
                    $this->notifyRulesExecuted($sender);
                    $this->success($result);
                }
                return;
            }
        }

        $validation = $this->validateArguments($sender, $args);
        if (!$validation["valid"]) {
            $this->fail(new CommandFailure($sender, $validation["type"], [
                ...$validation,
                "usage" => $this->getUsageLine($commandLabel)
            ]));
            return;
        }

        try {
            $this->argumentsList = $this->parseArguments($sender, $args);
            $context = new CommandContext($sender, $this->argumentsList, $commandLabel, $args, $this);
            $returnValue = $this->onRun($context);
            $this->notifyRulesExecuted($sender);
            $this->success(new CommandSuccess($sender, $this->argumentsList, $commandLabel, $returnValue));
        } catch (Throwable $throwable) {
            $this->fail(new CommandFailure($sender, CommandFailure::EXECUTION_ERROR, [
                "message" => $throwable->getMessage(),
                "exception" => $throwable
            ]));
        }
    }

    abstract protected function onRun(CommandContext $context): mixed;

    public function success(CommandSuccess $result): void {
    }

    public function fail(CommandFailure $result): void {
        $result->getSender()->sendMessage($result->getMessage());
    }

    /** @throws CommandConfigurationException */
    private function resolveMetadata(?string $name, ?string $description, array $aliases): array {
        $attributes = (new ReflectionClass($this))->getAttributes(CommandInfo::class);
        if ($attributes !== []) {
            /** @var CommandInfo $info */
            $info = $attributes[0]->newInstance();
            $name ??= $info->name;
            $description ??= $info->description;
            $aliases = $aliases === [] ? $info->aliases : $aliases;
        }

        if ($name === null || $name === "") {
            throw new CommandConfigurationException("Command name is required");
        }

        return [$name, $description ?? "", $aliases];
    }

    private function loadAttributePermission(): void {
        $reflection = new ReflectionClass($this);
        $permission = strtolower($this->getName()) . ".command";
        $isOp = true;

        $permissionAttributes = $reflection->getAttributes(CommandPermission::class);
        if ($permissionAttributes !== []) {
            /** @var CommandPermission $definition */
            $definition = $permissionAttributes[0]->newInstance();
            $permission = $definition->permission ?? $permission;
            $isOp = $definition->isOp;
        } else {
            $infoAttributes = $reflection->getAttributes(CommandInfo::class);
            if ($infoAttributes !== []) {
                /** @var CommandInfo $info */
                $info = $infoAttributes[0]->newInstance();
                $permission = $info->permission ?? $permission;
            }
        }

        if ($permission === "") {
            return;
        }

        $this->commandPermission = $permission;
        $this->registerPermission($permission, $isOp);
        $this->setPermission($permission);
        $this->addRule(new PermissionRule($permission));
    }

    public function registerPermission(string $permission, bool $isOp, ?string $description = null): void {
        $permissionManager = PermissionManager::getInstance();
        $permissionObject = $permissionManager->getPermission($permission);

        if ($permissionObject === null) {
            $permissionObject = new Permission($permission, $description ?? $this->getDescription());
            $permissionManager->addPermission($permissionObject);
        }

        $parentPermission = $permissionManager->getPermission(
            $isOp ? DefaultPermissions::ROOT_OPERATOR : DefaultPermissions::ROOT_USER
        );

        $parentPermission?->addChild($permission, true);
    }

    public function getCommandPermission(): string {
        return $this->commandPermission;
    }

    /** @throws CommandConfigurationException */
    private function loadAttributeArguments(): void {
        foreach ((new ReflectionClass($this))->getAttributes(CommandArgument::class) as $attribute) {
            /** @var CommandArgument $definition */
            $definition = $attribute->newInstance();
            if (!is_a($definition->type, Argument::class, true)) {
                throw new CommandConfigurationException("Command argument '{$definition->name}' must use an Argument class");
            }

            $this->addArgument(new $definition->type(
                $definition->name,
                $definition->optional,
                $definition->default
            ));
        }
    }
}
