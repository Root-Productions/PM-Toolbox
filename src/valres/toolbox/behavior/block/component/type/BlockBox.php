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
        return new self([-8.0, 0.0, -8.0], [16.0, 16.0, 16.0]);
    }

    public function toCollisionArray(): array {
        $origin = array_map("floatval", array_values($this->origin));
        $size = array_map("floatval", array_values($this->size));
        $minX = 8.0 + $origin[0];
        $minY = $origin[1];
        $minZ = 8.0 + $origin[2];

        return [
            "minX" => $minX,
            "minY" => $minY,
            "minZ" => $minZ,
            "maxX" => $minX + $size[0],
            "maxY" => $minY + $size[1],
            "maxZ" => $minZ + $size[2]
        ];
    }

    public function toArray(): array {
        return [
            "origin" => array_map("floatval", array_values($this->origin)),
            "size" => array_map("floatval", array_values($this->size))
        ];
    }
}
