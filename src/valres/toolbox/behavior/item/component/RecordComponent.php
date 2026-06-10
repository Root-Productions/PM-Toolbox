<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

final class RecordComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly int $comparatorSignal,
        private readonly float $duration,
        private readonly string $soundEvent
    ) {
        if ($this->comparatorSignal < 1 || $this->comparatorSignal > 13) {
            throw new ItemRegistryException("Component 'minecraft:record', value 'comparator_signal' must be between 1 and 13, got " . $this->comparatorSignal);
        }
    }

    public static function identifier(): string {
        return "minecraft:record";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "comparator_signal" => $this->comparatorSignal,
            "duration" => $this->duration,
            "sound_event" => $this->soundEvent
        ]);
    }
}
