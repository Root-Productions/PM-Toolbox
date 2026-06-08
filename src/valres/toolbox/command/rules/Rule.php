<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;

abstract class Rule {
    public function success(CommandSender $sender): void {}

    public function onExecuted(CommandSender $sender): void {}

    public function canExecute(CommandSender $sender): bool {
        return $this->canSee($sender);
    }

    abstract public function fail(CommandSender $sender): void;
    abstract public function canSee(CommandSender $sender): bool;
}
