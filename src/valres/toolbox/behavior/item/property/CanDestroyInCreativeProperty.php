<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Determines whether the item can break blocks while the player is in creative mode. */
final class CanDestroyInCreativeProperty extends DataDrivenItemProperty {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:can_destroy_in_creative";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
