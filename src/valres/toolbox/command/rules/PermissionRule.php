<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class PermissionRule extends Rule {
    public function __construct(
        private readonly string $permission,
        private readonly ?string $message = null
    ) {
    }

    public function fail(CommandSender $sender): void {
        $sender->sendMessage($this->message ?? TextFormat::RED . "You do not have permission to use this command.");
    }

    public function canSee(CommandSender $sender): bool {
        return $sender->hasPermission($this->permission);
    }
}
