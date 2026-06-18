<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockBox;

/** Defines the box targeted by player selection and block interaction. */
final class SelectionBoxComponent extends BlockComponent {
    public function __construct(private readonly bool|BlockBox $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:selection_box";
    }

    public static function box(array $origin, array $size): self {
        return new self(new BlockBox($origin, $size));
    }

    public function toNBT(): Tag {
        if (!$this->value instanceof BlockBox) {
            if (!$this->value) {
                return ComponentNbtHelper::compound(["enabled" => false]);
            }

            return ComponentNbtHelper::compound(BlockBox::cube()->toArray() + ["enabled" => true]);
        }

        return ComponentNbtHelper::compound($this->value->toArray() + ["enabled" => true]);
    }
}
