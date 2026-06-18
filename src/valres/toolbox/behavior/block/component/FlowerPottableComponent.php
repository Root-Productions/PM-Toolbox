<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

/** Allows the block to be placed inside a flower pot. */
final class FlowerPottableComponent extends BlockComponent {
    public static function identifier(): string {
        return "minecraft:flower_pottable";
    }

    public function toNBT(): Tag {
        return CompoundTag::create();
    }
}
