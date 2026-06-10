<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class DamageAbsorptionComponent extends DataDrivenItemComponent {
    /** @param string[] $absorbableCauses */
    public function __construct(private readonly array $absorbableCauses = ["all"]) {
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
