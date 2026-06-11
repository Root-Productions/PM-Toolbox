<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\ItemRange;

/** Defines damage, dismount and knockback behavior while the item is used. */
final class KineticWeaponComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly ?float $chargeDelay = null,
        private readonly ?float $hitboxMargin = null,
        private readonly ItemRange|array|null $reachRange = null,
        private readonly ItemRange|array|null $creativeReachRange = null,
        private readonly ?float $damageMultiplier = null,
        private readonly ?float $damageModifier = null,
        private readonly ?array $damageConditions = null,
        private readonly ?array $dismountConditions = null,
        private readonly ?array $knockbackConditions = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:kinetic_weapon";
    }

    public static function range(float $min, float $max): ItemRange {
        return new ItemRange($min, $max);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "delay" => $this->chargeDelay,
            "hitbox_margin" => $this->hitboxMargin,
            "reach" => $this->reachRange,
            "creative_reach" => $this->creativeReachRange,
            "damage_multiplier" => $this->damageMultiplier,
            "damage_modifier" => $this->damageModifier,
            "damage_conditions" => $this->damageConditions,
            "dismount_conditions" => $this->dismountConditions,
            "knockback_conditions" => $this->knockbackConditions
        ]);
    }
}
