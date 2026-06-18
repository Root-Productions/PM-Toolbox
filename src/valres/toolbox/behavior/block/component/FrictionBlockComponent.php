<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

/** Defines the friction applied to entities moving across the block. */
final class FrictionBlockComponent extends BlockComponent {
    public function __construct(private readonly float $friction) {
    }

    public static function identifier(): string {
        return "minecraft:friction";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->friction);
    }
}
