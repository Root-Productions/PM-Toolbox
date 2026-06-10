<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Allows the item to store other item stacks. */
final class StorageItemComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly int $maxSlots,
        private readonly ?bool $allowNestedStorageItems = null,
        private readonly ?array $allowedItems = null,
        private readonly ?array $bannedItems = null
    ) {
        if ($this->maxSlots < 1 || $this->maxSlots > 64) {
            throw new ItemRegistryException("Component 'minecraft:storage_item', value 'max_slots' must be between 1 and 64, got " . $this->maxSlots);
        }
    }

    public static function identifier(): string {
        return "minecraft:storage_item";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "max_slots" => $this->maxSlots,
            "allow_nested_storage_items" => $this->allowNestedStorageItems,
            "allowed_items" => $this->allowedItems,
            "banned_items" => $this->bannedItems
        ]);
    }
}
