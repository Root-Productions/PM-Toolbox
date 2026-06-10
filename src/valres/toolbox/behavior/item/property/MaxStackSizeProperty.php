<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the maximum number of this item that can stack in one slot. */
final class MaxStackSizeProperty extends DataDrivenItemProperty {
    /** @throws ItemRegistryException */
    public function __construct(private readonly int $value) {
        if ($this->value < 1 || $this->value > 64) {
            throw new ItemRegistryException("Property 'minecraft:max_stack_size' must be between 1 and 64, got " . $this->value);
        }
    }

    public static function identifier(): string {
        return "minecraft:max_stack_size";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
