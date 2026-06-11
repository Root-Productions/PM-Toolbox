<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class DisplayNameBlockComponent extends BlockComponent {
    public function __construct(private readonly string $name) {
    }

    public static function identifier(): string {
        return "minecraft:display_name";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->name);
    }
}
