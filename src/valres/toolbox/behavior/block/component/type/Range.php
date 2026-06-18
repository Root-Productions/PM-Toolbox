<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

/** Represents an inclusive numeric range used by block components. */
final class Range {
    public function __construct(
        private readonly int|float $min,
        private readonly int|float $max
    ) {
    }

    /** @return array{min: float|int, max: float|int} */
    public function toArray(): array {
        return [
            "min" => $this->min,
            "max" => $this->max
        ];
    }
}
