<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

final class BlockBox {
    public function __construct(
        private readonly array $origin,
        private readonly array $size
    ) {
    }

    public static function cube(): self {
        return new self([-8, 0, -8], [16, 16, 16]);
    }

    public function toArray(): array {
        return [
            "origin" => $this->origin,
            "size" => $this->size
        ];
    }
}
