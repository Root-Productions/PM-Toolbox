<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

final class RandomOffsetAxis {
    public function __construct(
        private readonly Range $range,
        private readonly int $steps = 0
    ) {
    }

    public function toArray(): array {
        return [
            "range" => $this->range->toArray(),
            "steps" => $this->steps
        ];
    }
}
