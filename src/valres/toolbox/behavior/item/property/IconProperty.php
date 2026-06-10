<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\CompoundTag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the texture or texture set used for the item's UI icon. */
final class IconProperty extends DataDrivenItemProperty {
    public function __construct(private readonly string $icon) {
    }

    public static function identifier(): string {
        return "minecraft:icon";
    }

    public function toNBT(): CompoundTag {
        return ComponentNbtHelper::compound([
            "texture" => $this->icon,
            "textures" => [
                "default" => $this->icon
            ]
        ]);
    }
}
