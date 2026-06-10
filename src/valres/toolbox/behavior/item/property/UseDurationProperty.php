<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines how long the item can be used or charged in older item formats. */
final class UseDurationProperty extends DataDrivenItemProperty {
    public function __construct(private readonly int|float $value) {
    }

    public static function identifier(): string {
        return "use_duration";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
