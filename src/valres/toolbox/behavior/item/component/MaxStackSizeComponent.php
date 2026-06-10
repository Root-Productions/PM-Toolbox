<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

final class MaxStackSizeComponent extends LegacyItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(private readonly int $value) {
        if ($this->value <= 0 or $this->value > 64) {
            throw new ItemRegistryException("Component 'minecraft:max_stack_size' must be between 1 and 64, got " . $this->value);
        }
    }

    public static function identifier(): string {
        return "minecraft:max_stack_size";
    }

    public function toNBT(): Tag {
        return new IntTag($this->value);
    }
}