<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines vanilla sound events played when attacking while holding the item. */
final class SwingSoundsComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly ?string $attackMiss = null,
        private readonly ?string $attackHit = null,
        private readonly ?string $attackCriticalHit = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:swing_sounds";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "attack_miss" => $this->attackMiss,
            "attack_hit" => $this->attackHit,
            "attack_critical_hit" => $this->attackCriticalHit
        ]);
    }
}
