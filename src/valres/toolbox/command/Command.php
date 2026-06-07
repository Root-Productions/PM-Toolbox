<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use pocketmine\command\Command as PMCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use valres\toolbox\command\result\CommandFailure;
use valres\toolbox\command\result\CommandSuccess;
use valres\toolbox\command\rules\RuleTrait;
use valres\toolbox\ToolboxLoader;

abstract class Command extends PMCommand implements PluginOwned {
    use RuleTrait;

    private Plugin $plugin;

    protected CommandSender $sender;

    /** @var array<SubCommand> */
    private array $subCommands = [];

    protected ArgumentsList $argumentsList;

    public function __construct(string $name, string $description, array $aliases = []) {
        parent::__construct($name, $description, null, $aliases);

        $this->plugin = ToolboxLoader::getLoader();
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!empty($args)) {

        }

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
    }

    abstract public function success(CommandSuccess $result): void;
    abstract public function fail(CommandFailure $result): void;
}