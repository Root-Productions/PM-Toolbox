<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockBox;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

final class CollisionBoxComponent extends BlockComponent {
    /** @param bool|BlockBox|BlockBox[] $value */
    public function __construct(private readonly bool|BlockBox|array $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:collision_box";
    }

    public static function box(array $origin, array $size): self {
        return new self(new BlockBox($origin, $size));
    }

    public function toNBT(): Tag {
        if ($this->value instanceof BlockBox) {
            return ComponentNbtHelper::tag($this->value->toArray());
        }

        if (is_array($this->value)) {
            return ComponentNbtHelper::tag(array_map(
                static fn(BlockBox $box): array => $box->toArray(),
                $this->value
            ));
        }

        return ComponentNbtHelper::tag($this->value);
    }
}
