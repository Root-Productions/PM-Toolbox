<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

/** Defines use duration, movement speed and vibration behavior while using the item. */
final class UseModifiersComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly float $useDuration,
        private readonly ?bool $emitVibrations = null,
        private readonly ?float $movementModifier = null,
        private readonly ?string $startSound = null,
    ) {
        if ($this->movementModifier !== null && ($this->movementModifier < 0.0 or $this->movementModifier > 1.0)) {
            throw new ItemRegistryException("Component 'minecraft:use_modifiers', value 'movement_modifier' must be between 0.0 and 1.0, got " . $this->movementModifier);
        }
    }

    public static function identifier(): string {
        return "minecraft:use_modifiers";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "use_duration" => $this->useDuration,
            "emit_vibrations" => $this->emitVibrations,
            "movement_modifier" => $this->movementModifier,
            "start_sound" => $this->startSound
        ]);
    }
}
