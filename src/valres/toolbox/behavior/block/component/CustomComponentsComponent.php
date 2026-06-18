<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Enables the legacy custom block interaction and placement hooks. */
class CustomComponentsComponent extends BlockComponent {
    public function __construct(
        private readonly bool $hasPlayerInteract = true,
        private readonly bool $hasPlayerPlacing = true,
        private readonly bool $isV1Component = true,
    ) {
    }

    public static function identifier(): string {
        return "minecraft:custom_components";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "hasPlayerInteract" => $this->hasPlayerInteract,
            "hasPlayerPlacing" => $this->hasPlayerPlacing,
            "isV1Component" => $this->isV1Component
        ]);
    }
}
