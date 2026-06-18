<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockVisual;

/** Defines the geometry and materials used when the block is shown as an item. */
final class ItemVisualComponent extends BlockComponent {
    public function __construct(private readonly BlockVisual $visual) {
    }

    public static function identifier(): string {
        return "minecraft:item_visual";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->visual->toArray());
    }
}
