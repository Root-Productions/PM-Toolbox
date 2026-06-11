<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

final class DurabilityThreshold implements ItemComponentValue {
    public function __construct(
        private readonly int $durability,
        private readonly ?string $particleType = null,
        private readonly ?string $soundEvent = null
    ) {
    }

    public function toArray(): array {
        return array_filter([
            "durability" => $this->durability,
            "particle_type" => $this->particleType,
            "sound_event" => $this->soundEvent
        ], static fn(mixed $value): bool => $value !== null);
    }
}
