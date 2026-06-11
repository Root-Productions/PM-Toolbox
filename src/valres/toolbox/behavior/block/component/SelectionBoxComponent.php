<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockBox;

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
        return ComponentNbtHelper::tag($this->value instanceof BlockBox ? $this->value->toArray() : $this->value);
    }
}
