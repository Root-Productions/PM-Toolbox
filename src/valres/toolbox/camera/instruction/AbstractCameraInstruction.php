<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use InvalidArgumentException;
use pocketmine\player\Player;

abstract class AbstractCameraInstruction implements CameraInstruction {
    public function send(Player $player): void {
        $player->getNetworkSession()->sendDataPacket($this->toPacket());
    }

    protected static function requireNonNegative(float $value, string $name): float {
        if ($value < 0) {
            throw new InvalidArgumentException("{$name} must be greater than or equal to 0.");
        }

        return $value;
    }

    protected static function requireRange(float $value, float $min, float $max, string $name): float {
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException("{$name} must be between {$min} and {$max}.");
        }

        return $value;
    }
}
