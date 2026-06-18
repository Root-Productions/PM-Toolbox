<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Defines how much light is absorbed while passing through the block. */
final class LightDampeningComponent extends BlockComponent {
    public function __construct(private readonly int $level) {
    }

    public static function identifier(): string {
        return "minecraft:light_dampening";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->level);
    }
}
