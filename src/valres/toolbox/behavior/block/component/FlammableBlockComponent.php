<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Controls how easily the block catches fire and is destroyed by it. */
final class FlammableBlockComponent extends BlockComponent {
    public function __construct(
        private readonly bool|int $catchChanceModifier = true,
        private readonly ?int $destroyChanceModifier = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:flammable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag(is_bool($this->catchChanceModifier) ? $this->catchChanceModifier : [
            "catch_chance_modifier" => $this->catchChanceModifier,
            "destroy_chance_modifier" => $this->destroyChanceModifier
        ]);
    }
}
