<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Makes legacy item stacks separate by data value. */
final class StackByDataComponent extends LegacyItemComponent {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:stack_by_data";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
