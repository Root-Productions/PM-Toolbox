<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class OnlyConsoleRule extends Rule {
    public function fail(CommandSender $sender): void {
        $sender->sendMessage(TextFormat::RED . "This command can only be used in console.");
    }

    public function canSee(CommandSender $sender): bool {
        return !$sender instanceof Player;
    }
}