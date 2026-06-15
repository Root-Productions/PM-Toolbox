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
        $enabled = true;
        $boxes = [BlockBox::cube()];

        if ($this->value instanceof BlockBox) {
            $boxes = [$this->value];
        } elseif (is_array($this->value)) {
            $boxes = $this->value;
        } else {
            $enabled = $this->value;
        }

        return ComponentNbtHelper::compound([
            "enabled" => $enabled,
            "boxes" => array_map(
                static fn(BlockBox $box): array => $box->toCollisionArray(),
                $boxes
            )
        ]);
    }
}
