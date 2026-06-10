<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Allows the item to take damage before breaking. */
final class DurabilityComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly int $maxDurability,
        private readonly ?int $damageChanceMin = null,
        private readonly ?int $damageChanceMax = null
    ) {
        if ($this->maxDurability < 0 || $this->maxDurability > 32767) {
            throw new ItemRegistryException("Component 'minecraft:durability', value 'max_durability' must be between 0 and 32767, got " . $this->maxDurability);
        }
    }

    public static function identifier(): string {
        return "minecraft:durability";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "max_durability" => $this->maxDurability,
            "damage_chance" => ($this->damageChanceMin !== null && $this->damageChanceMax !== null) ? [
                "min" => $this->damageChanceMin,
                "max" => $this->damageChanceMax
            ] : null
        ]);
    }
}
