<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the item rarity and resulting name color. */
final class RarityProperty extends DataDrivenItemProperty {
    public const COMMON = "common";
    public const UNCOMMON = "uncommon";
    public const RARE = "rare";
    public const EPIC = "epic";

    public function __construct(private readonly string $value) {
    }

    public static function identifier(): string {
        return "rarity";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
