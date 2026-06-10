<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class DyeableComponent extends DataDrivenItemComponent {
    public function __construct(private readonly ?string $defaultColor = null) {
    }

    public static function identifier(): string {
        return "minecraft:dyeable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["default_color" => $this->defaultColor]);
    }
}
