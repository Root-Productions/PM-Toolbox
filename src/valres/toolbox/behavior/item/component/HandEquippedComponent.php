<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Makes the item render like a hand-held tool in legacy format. */
final class HandEquippedComponent extends LegacyItemComponent {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:hand_equipped";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
