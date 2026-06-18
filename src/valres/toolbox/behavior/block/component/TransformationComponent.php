<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Applies translation, rotation and scale transforms to the block model. */
final class TransformationComponent extends BlockComponent {
    public function __construct(
        private readonly ?array $translation = null,
        private readonly ?array $rotation = null,
        private readonly ?array $rotationPivot = null,
        private readonly ?array $scale = null,
        private readonly ?array $scalePivot = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:transformation";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "translation" => $this->translation,
            "rotation" => $this->rotation,
            "rotation_pivot" => $this->rotationPivot,
            "scale" => $this->scale,
            "scale_pivot" => $this->scalePivot
        ]);
    }
}
