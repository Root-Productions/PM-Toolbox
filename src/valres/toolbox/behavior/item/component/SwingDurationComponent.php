<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Defines the base duration, in seconds, of the player's swing animation. */
final class SwingDurationComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(private readonly float $value) {
        if ($this->value < 0.0) {
            throw new ItemRegistryException("Component 'minecraft:swing_duration', value 'value' must be at least 0.0, got " . $this->value);
        }
    }

    public static function identifier(): string {
        return "minecraft:swing_duration";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["value" => $this->value]);
    }
}
