<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Determines whether different data values stack separately. */
final class StackedByDataProperty extends DataDrivenItemProperty {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:stacked_by_data";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
