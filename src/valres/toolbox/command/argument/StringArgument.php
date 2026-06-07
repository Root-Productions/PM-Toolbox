<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class StringArgument extends Argument {
    public function getTypeName(): string {
        return "string";
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return true;
    }

    public function parse(CommandSender $sender, string $arg): string {
        return $arg;
    }
}