<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\RandomOffsetAxis;

/** Applies a deterministic random visual offset along configured axes. */
final class RandomOffsetComponent extends BlockComponent {
    public function __construct(
        private readonly ?RandomOffsetAxis $x = null,
        private readonly ?RandomOffsetAxis $y = null,
        private readonly ?RandomOffsetAxis $z = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:random_offset";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "x" => $this->x?->toArray(),
            "y" => $this->y?->toArray(),
            "z" => $this->z?->toArray()
        ]);
    }
}
