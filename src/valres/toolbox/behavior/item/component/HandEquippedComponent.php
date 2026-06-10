<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Tag;

final class HandEquippedComponent extends LegacyItemComponent {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:hand_equipped";
    }

    public function toNBT(): Tag {
        return new ByteTag($this->value ? 1 : 0);
    }
}