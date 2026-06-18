<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

/** Defines the range and stepping used for random offset on one axis. */
final class RandomOffsetAxis {
    public function __construct(
        private readonly Range $range,
        private readonly int $steps = 0
    ) {
    }

    /** @return array{range: array{min: float|int, max: float|int}, steps: int} */
    public function toArray(): array {
        return [
            "range" => $this->range->toArray(),
            "steps" => $this->steps
        ];
    }
}
