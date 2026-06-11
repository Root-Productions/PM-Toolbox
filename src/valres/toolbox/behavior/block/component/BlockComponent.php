<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

abstract class BlockComponent {
    abstract public static function identifier(): string;

    public function getIdentifier(): string {
        return static::identifier();
    }

    abstract public function toNBT(): Tag;
}
