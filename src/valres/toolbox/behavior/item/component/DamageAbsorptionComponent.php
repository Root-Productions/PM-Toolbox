<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\DamageCause;

/** Defines damage causes that can be absorbed by the item. */
final class DamageAbsorptionComponent extends DataDrivenItemComponent {
    /** @param array<int, DamageCause|string> $absorbableCauses */
    public function __construct(private readonly array $absorbableCauses = [DamageCause::ALL]) {
    }

    public static function identifier(): string {
        return "minecraft:damage_absorption";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "absorbable_causes" => $this->absorbableCauses
        ]);
    }
}
