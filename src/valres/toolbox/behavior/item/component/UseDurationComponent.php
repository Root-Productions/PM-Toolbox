<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines how long the item can be used in legacy format. */
final class UseDurationComponent extends LegacyItemComponent {
    public function __construct(private readonly int $value) {
    }

    public static function identifier(): string {
        return "minecraft:use_duration";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
