<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class ReplaceableComponent extends BlockComponent {
    public static function identifier(): string {
        return "minecraft:replaceable";
    }

    public function toNBT(): Tag {
        return CompoundTag::create();
    }
}
