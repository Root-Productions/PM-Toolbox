<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class LeashableComponent extends BlockComponent {
    public function __construct(private readonly ?array $offset = null) {
    }

    public static function identifier(): string {
        return "minecraft:leashable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "offset" => $this->offset
        ]);
    }
}
