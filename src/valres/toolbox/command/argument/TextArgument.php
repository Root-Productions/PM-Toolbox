<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class TextArgument extends StringArgument {
    public function getTypeName(): string {
        return "text";
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return $test !== "";
    }

    public function getMaxLength(): int {
        return PHP_INT_MAX;
    }
}