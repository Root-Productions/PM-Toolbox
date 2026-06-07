<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;

abstract class Rule {
    public function success(CommandSender $sender): void {}

    abstract public function fail(CommandSender $sender): void;
    abstract public function canSee(CommandSender $sender): bool;
}