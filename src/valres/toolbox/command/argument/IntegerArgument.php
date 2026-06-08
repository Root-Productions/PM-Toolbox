<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class IntegerArgument extends Argument {
    public function getTypeName(): string {
        return "int";
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_INT;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return preg_match('/^-?\d+$/', $test) === 1;
    }

    public function parse(CommandSender $sender, string $arg): int {
        return (int) $arg;
    }
}
