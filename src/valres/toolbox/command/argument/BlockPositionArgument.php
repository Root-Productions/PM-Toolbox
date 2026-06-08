<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;

class BlockPositionArgument extends Vector3Argument {
    public function isValidCoordinate(string $coordinate, bool $locatable): bool {
        return (bool) preg_match(
            "/^(?:" . ($locatable ? "~[+-]?" : "-?") . "\\d+" . ($locatable ? "|~|[+-]?\\d+" : "") . ")$/",
            $coordinate
        );
    }

    public function parse(CommandSender $sender, string $arg): mixed {
        $v = parent::parse($sender, $arg);
        return $v->floor();
    }
}
