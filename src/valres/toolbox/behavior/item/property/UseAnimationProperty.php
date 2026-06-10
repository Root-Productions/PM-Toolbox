<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines which first/third-person animation plays while using the item. */
final class UseAnimationProperty extends DataDrivenItemProperty {
    public const EAT = "eat";
    public const DRINK = "drink";
    public const BOW = "bow";
    public const BLOCK = "block";
    public const CAMERA = "camera";
    public const CROSSBOW = "crossbow";
    public const NONE = "none";
    public const BRUSH = "brush";
    public const SPEAR = "spear";
    public const SPYGLASS = "spyglass";

    public function __construct(private readonly string $value) {
    }

    public static function identifier(): string {
        return "minecraft:use_animation";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
