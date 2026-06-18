<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\SupportShape;

/** Defines the support shape exposed to blocks placed against this block. */
final class SupportComponent extends BlockComponent {
    public function __construct(private readonly SupportShape|string $shape) {
    }

    public static function identifier(): string {
        return "minecraft:support";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "shape" => $this->shape instanceof SupportShape ? $this->shape->value : $this->shape
        ]);
    }
}
