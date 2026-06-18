<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

/** Controls whether explosions can destroy the block and its resistance. */
final class DestructibleByExplosionComponent extends BlockComponent {
    public function __construct(private readonly bool|float|int $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:destructible_by_explosion";
    }

    public static function resistance(float|int $explosionResistance): self {
        return new self($explosionResistance);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag(is_bool($this->value) ? $this->value : [
            "explosion_resistance" => $this->value
        ]);
    }
}
