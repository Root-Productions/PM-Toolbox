<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Allows the item to be used as furnace fuel for a duration. */
final class FuelComponent extends DataDrivenItemComponent {
    public function __construct(private readonly float $duration) {
    }

    public static function identifier(): string {
        return "minecraft:fuel";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["duration" => $this->duration]);
    }
}
