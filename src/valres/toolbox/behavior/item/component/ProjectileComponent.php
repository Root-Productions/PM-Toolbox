<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines the projectile entity launched by this item. */
final class ProjectileComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly string $projectileEntity,
        private readonly ?float $minimumCriticalPower = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:projectile";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "projectile_entity" => $this->projectileEntity,
            "minimum_critical_power" => $this->minimumCriticalPower
        ]);
    }
}
