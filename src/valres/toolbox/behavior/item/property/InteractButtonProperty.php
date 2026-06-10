<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Controls whether touch controls show an interact button and optional text. */
final class InteractButtonProperty extends DataDrivenItemProperty {
    public function __construct(private readonly bool|string $value = true) {
    }

    public static function identifier(): string {
        return "interact_button";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
