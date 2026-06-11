<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class GeometryComponent extends BlockComponent {
    /** @param array<string, bool|string> $boneVisibility */
    public function __construct(
        private readonly string $identifier,
        private readonly ?string $culling = null,
        private readonly ?string $cullingLayer = null,
        private readonly bool|array|null $uvLock = null,
        private readonly array $boneVisibility = []
    ) {
    }

    public static function identifier(): string {
        return "minecraft:geometry";
    }

    public static function vanilla(string $identifier): self {
        return new self($identifier);
    }

    public function toNBT(): Tag {
        if ($this->culling === null && $this->cullingLayer === null && $this->uvLock === null && $this->boneVisibility === []) {
            return ComponentNbtHelper::tag($this->identifier);
        }

        return ComponentNbtHelper::compound([
            "identifier" => $this->identifier,
            "culling" => $this->culling,
            "culling_layer" => $this->cullingLayer,
            "uv_lock" => $this->uvLock,
            "bone_visibility" => $this->boneVisibility === [] ? null : $this->boneVisibility
        ]);
    }
}
