<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines damage, dismount and knockback behavior while the item is used. */
final class KineticWeaponComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly ?float $chargeDelay = null,
        private readonly ?float $hitboxMargin = null,
        private readonly ?array $reachRange = null,
        private readonly ?array $creativeReachRange = null,
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

    public static function range(float $min, float $max): array {
        return [
            "min" => $min,
            "max" => $max
        ];
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "charge_delay" => $this->chargeDelay,
            "hitbox_margin" => $this->hitboxMargin,
            "reach_range" => $this->reachRange,
            "creative_reach_range" => $this->creativeReachRange,
            "damage_multiplier" => $this->damageMultiplier,
            "damage_modifier" => $this->damageModifier,
            "damage_conditions" => $this->damageConditions,
            "dismount_conditions" => $this->dismountConditions,
            "knockback_conditions" => $this->knockbackConditions
        ]);
    }
}
