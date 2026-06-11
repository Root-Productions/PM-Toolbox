<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\DurabilityThreshold;

/** Triggers particles or sounds when durability thresholds are reached. */
final class DurabilitySensorComponent extends DataDrivenItemComponent {
    /** @param array<int, DurabilityThreshold|array{durability: int, particle_type?: string, sound_event?: string}> $thresholds */
    public function __construct(private readonly array $thresholds) {
    }

    public static function identifier(): string {
        return "minecraft:durability_sensor";
    }

    public static function threshold(int $durability, ?string $particleType = null, ?string $soundEvent = null): DurabilityThreshold {
        return new DurabilityThreshold($durability, $particleType, $soundEvent);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "durability_thresholds" => $this->thresholds
        ]);
    }
}
