<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

final class DestroySpeed implements ItemComponentValue {
    public function __construct(
        private readonly BlockDescriptor|string|array $block,
        private readonly int $speed
    ) {
    }

    public function toArray(): array {
        return [
            "block" => $this->block instanceof BlockDescriptor ? $this->block->toArray() : $this->block,
            "speed" => $this->speed
        ];
    }
}
