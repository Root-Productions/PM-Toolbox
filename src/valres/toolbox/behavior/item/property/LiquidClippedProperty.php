<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Determines whether item use traces can pass through liquids. */
final class LiquidClippedProperty extends DataDrivenItemProperty {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "liquid_clipped";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
