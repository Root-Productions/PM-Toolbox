<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines how much extra attack damage the item deals. */
final class DamageProperty extends DataDrivenItemProperty {
    /** @throws ItemRegistryException */
    public function __construct(private readonly int $value) {
        if ($this->value < 0 || $this->value > 32767) {
            throw new ItemRegistryException("Property 'minecraft:damage' must be between 0 and 32767, got " . $this->value);
        }
    }

    public static function identifier(): string {
        return "minecraft:damage";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
