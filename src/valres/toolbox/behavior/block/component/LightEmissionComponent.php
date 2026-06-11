<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class LightEmissionComponent extends BlockComponent {
    public function __construct(private readonly int $level) {
    }

    public static function identifier(): string {
        return "minecraft:light_emission";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->level);
    }
}
