<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class GeometryComponent extends BlockComponent {
    /** @param array<string, bool|string> $boneVisibility */
    public function __construct(
        private readonly string $identifier = "minecraft:geometry.full_block",
        private readonly string $culling = "",
        private readonly string $cullingLayer = "minecraft:culling_layer.undefined",
        private readonly bool $uvLock = false,
        private array $boneVisibility = [],
        private readonly bool $ignoreGeometryForIsSolid = true,
        private readonly bool $needsLegacyTopRotation = false,
        private readonly bool $useBlockTypeLightAbsorption = false
    ) {
    }

    public static function identifier(): string {
        return "minecraft:geometry";
    }

    public static function vanilla(string $identifier): self {
        return new self($identifier);
    }

    public function add(string $boneName, bool|string $visibility): self {
        $this->boneVisibility[$boneName] = $visibility;
        return $this;
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "bone_visibility" => $this->boneVisibility === [] ? CompoundTag::create() : $this->serializeBoneVisibility(),
            "identifier" => $this->identifier,
            "culling" => $this->culling,
            "culling_layer" => $this->cullingLayer,
            "uv_lock" => $this->uvLock,
            "ignoreGeometryForIsSolid" => $this->ignoreGeometryForIsSolid,
            "needsLegacyTopRotation" => $this->needsLegacyTopRotation,
            "useBlockTypeLightAbsorption" => $this->useBlockTypeLightAbsorption
        ]);
    }

    /** @return array<string, bool|array{expression: string, version: int}> */
    private function serializeBoneVisibility(): array {
        $visibility = [];
        foreach ($this->boneVisibility as $boneName => $value) {
            $visibility[$boneName] = is_bool($value) ? $value : [
                "expression" => $value,
                "version" => 12
            ];
        }

        return $visibility;
    }
}
