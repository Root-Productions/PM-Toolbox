<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the color used for the item's hover name text. */
final class HoverTextColorProperty extends DataDrivenItemProperty {
    public function __construct(private readonly string $color) {
    }

    public static function identifier(): string {
        return "minecraft:hover_text_color";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->color);
    }
}
