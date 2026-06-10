<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Allows the item to receive enchantments for a specific slot type. */
final class EnchantableComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly string $slot,
        private readonly int $value
    ) {
        if ($this->value < 0 || $this->value > 255) {
            throw new ItemRegistryException("Component 'minecraft:enchantable', value 'value' must be between 0 and 255, got " . $this->value);
        }
    }

    public static function identifier(): string {
        return "minecraft:enchantable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "slot" => $this->slot,
            "value" => $this->value
        ]);
    }
}
