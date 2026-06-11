<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\MovableType;

final class MovableComponent extends BlockComponent {
    public function __construct(
        private readonly MovableType|string $movementType = MovableType::PUSH_PULL,
        private readonly ?string $sticky = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:movable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "movement_type" => $this->movementType instanceof MovableType ? $this->movementType->value : $this->movementType,
            "sticky" => $this->sticky
        ]);
    }
}
