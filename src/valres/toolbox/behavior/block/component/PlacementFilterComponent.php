<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockFace;

final class PlacementFilterComponent extends BlockComponent {
    /** @param array<int, array<string, mixed>> $conditions */
    public function __construct(private readonly array $conditions) {
    }

    public static function identifier(): string {
        return "minecraft:placement_filter";
    }

    /** @param BlockFace[]|string[] $allowedFaces */
    public static function condition(array $allowedFaces = [], array $blockFilter = []): array {
        return [
            "allowed_faces" => $allowedFaces === [] ? null : array_map(
                static fn(BlockFace|string $face): string => $face instanceof BlockFace ? $face->value : $face,
                $allowedFaces
            ),
            "block_filter" => $blockFilter === [] ? null : $blockFilter
        ];
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "conditions" => $this->conditions
        ]);
    }
}
