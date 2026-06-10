<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

/** Defines the texture or texture set used for the item's UI icon. */
final class IconProperty extends DataDrivenItemProperty {
    public function __construct(private readonly string $icon) {
    }

    public static function identifier(): string {
        return "minecraft:icon";
    }

    public function toNBT(): CompoundTag {
        return CompoundTag::create()
            ->setTag("texture", new StringTag($this->icon))
            ->setTag(
                "textures",
                CompoundTag::create()
                    ->setTag("default", new StringTag($this->icon))
            );
    }
}
