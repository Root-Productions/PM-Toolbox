<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;

final class MaxDamageComponent extends LegacyItemComponent {
    public function __construct(private readonly int $value) {
    }

    public static function identifier(): string {
        return "minecraft:max_damage";
    }

    public function toNBT(): Tag {
        return new IntTag($this->value);
    }
}