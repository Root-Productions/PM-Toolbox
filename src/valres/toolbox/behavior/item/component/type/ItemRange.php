<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

final class ItemRange implements ItemComponentValue {
    public function __construct(
        private readonly int|float $min,
        private readonly int|float $max
    ) {
    }

    public function toArray(): array {
        return [
            "min" => $this->min,
            "max" => $this->max
        ];
    }
}
