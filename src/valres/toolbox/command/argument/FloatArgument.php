<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class FloatArgument extends Argument {
    public function getTypeName(): string {
        return "float";
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_FLOAT;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return is_numeric($test);
    }

    public function parse(CommandSender $sender, string $arg): float {
        return (float) $arg;
    }
}
