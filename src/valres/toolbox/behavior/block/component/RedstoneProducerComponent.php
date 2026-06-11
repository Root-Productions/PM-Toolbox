<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockFace;

final class RedstoneProducerComponent extends BlockComponent {
    /** @param BlockFace[]|string[] $connectedFaces */
    public function __construct(
        private readonly int $power,
        private readonly BlockFace|string $stronglyPoweredFace,
        private readonly array $connectedFaces = [],
        private readonly ?bool $transformRelative = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:redstone_producer";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "power" => $this->power,
            "strongly_powered_face" => $this->stronglyPoweredFace instanceof BlockFace ? $this->stronglyPoweredFace->value : $this->stronglyPoweredFace,
            "connected_faces" => $this->connectedFaces === [] ? null : array_map(
                static fn(BlockFace|string $face): string => $face instanceof BlockFace ? $face->value : $face,
                $this->connectedFaces
            ),
            "transform_relative" => $this->transformRelative
        ]);
    }
}
