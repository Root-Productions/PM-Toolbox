<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

class CompostableComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly float $compostingChance,
    ) {
        if ($this->compostingChance < 0 or $this->compostingChance > 100) {
            throw new ItemRegistryException("Component 'minecraft:compostable', value 'composting_chance' must be between 0 and 100, got " . $this->compostingChance);
        }
    }

    public static function identifier(): string {
        return "minecraft:compostable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["composting_chance" => $this->compostingChance]);
    }
}
