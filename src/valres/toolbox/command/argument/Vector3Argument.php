<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class Vector3Argument extends Argument {
    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_POSITION;
    }

    public function getTypeName(): string {
        return "x y z";
    }

    public function canParse(CommandSender $sender, string $test): bool {
        $coords = explode(" ", $test);
        if (count($coords) !== 3) {
            return false;
        }

        foreach ($coords as $coord) {
            if (!$this->isValidCoordinate($coord, $sender instanceof Entity)) {
                return false;
            }
        }

        return true;
    }

    public function isValidCoordinate(string $coordinate, bool $locatable): bool {
        return (bool) preg_match(
            "/^(?:" . ($locatable ? "~[+-]?" : "-?") . "(?:\\d+|\\d*\\.\\d+)" . ($locatable ? "|~|[+-]?(?:\\d+|\\d*\\.\\d+)" : "") . ")$/",
            $coordinate
        );
    }

    public function parse(CommandSender $sender, string $arg): mixed {
        $coords = explode(" ", $arg);
        $vals = [];

        foreach ($coords as $k => $coord) {
            $offset = 0;

            if ($sender instanceof Entity && str_starts_with($coord, "~")) {
                $offset = substr($coord, 1) ?: 0;

                $position = $sender->getPosition();
                $coord = match ($k) {
                    0 => $position->x,
                    1 => $position->y,
                    2 => $position->z,
                };
            }

            $vals[] = (float) $coord + (float) $offset;
        }

        return new Vector3(...$vals);
    }

    public function getMaxLength(): int {
        return 3;
    }
}
