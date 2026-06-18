<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Sets the minimum fall distance required to trigger fall-on behavior. */
final class EntityFallOnComponent extends BlockComponent {
    public function __construct(private readonly float|int $minFallDistance) {
    }

    public static function identifier(): string {
        return "minecraft:entity_fall_on";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "min_fall_distance" => $this->minFallDistance
        ]);
    }
}
