<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use valres\toolbox\rcon\RconCommandSender;

class OnlyRCONRule extends Rule {
    public function fail(CommandSender $sender): void {
        $sender->sendMessage(TextFormat::RED . "This command can only be used with RCON.");
    }

    public function canSee(CommandSender $sender): bool {
        return $sender instanceof RconCommandSender;
    }
}