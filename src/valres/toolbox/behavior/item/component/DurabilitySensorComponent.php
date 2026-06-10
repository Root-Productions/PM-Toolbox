<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class DurabilitySensorComponent extends DataDrivenItemComponent {
    /** @param array<int, array{durability: int, particle_type?: string, sound_event?: string}> $thresholds */
    public function __construct(private readonly array $thresholds) {
    }

    public static function identifier(): string {
        return "minecraft:durability_sensor";
    }

    public static function threshold(int $durability, ?string $particleType = null, ?string $soundEvent = null): array {
        return array_filter([
            "durability" => $durability,
            "particle_type" => $particleType,
            "sound_event" => $soundEvent
        ], static fn(mixed $value): bool => $value !== null);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "durability_thresholds" => $this->thresholds
        ]);
    }
}
