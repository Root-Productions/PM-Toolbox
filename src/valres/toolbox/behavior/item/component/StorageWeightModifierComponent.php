<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Defines the extra weight this item adds inside storage items. */
final class StorageWeightModifierComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(private readonly int $weightInStorageItem) {
        if ($this->weightInStorageItem < 0 || $this->weightInStorageItem > 64) {
            throw new ItemRegistryException("Component 'minecraft:storage_weight_modifier', value 'weight_in_storage_item' must be between 0 and 64, got " . $this->weightInStorageItem);
        }
    }

    public static function identifier(): string {
        return "minecraft:storage_weight_modifier";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["weight_in_storage_item" => $this->weightInStorageItem]);
    }
}
