<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class LootComponent extends BlockComponent {
    public function __construct(private readonly string $lootTable) {
    }

    public static function identifier(): string {
        return "minecraft:loot";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->lootTable);
    }
}
