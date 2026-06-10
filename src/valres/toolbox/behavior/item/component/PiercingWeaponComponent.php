<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Defines how many entities a projectile weapon can pierce through. */
final class PiercingWeaponComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(private readonly int $level) {
        if ($this->level < 0) {
            throw new ItemRegistryException("Component 'minecraft:piercing_weapon', value 'level' must be at least 0, got " . $this->level);
        }
    }

    public static function identifier(): string {
        return "minecraft:piercing_weapon";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["level" => $this->level]);
    }
}
