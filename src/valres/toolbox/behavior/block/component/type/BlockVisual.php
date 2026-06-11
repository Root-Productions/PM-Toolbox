<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

use pocketmine\nbt\tag\CompoundTag;

final class BlockVisual {
    /**
     * @param string|array<string, mixed> $geometry
     * @param array<string, MaterialInstance|string|array<string, mixed>> $materialInstances
     */
    public function __construct(
        private readonly string|array $geometry,
        private readonly array $materialInstances
    ) {
    }

    public function toArray(): array {
        $materials = [];
        foreach ($this->materialInstances as $name => $instance) {
            $materials[$name] = $instance instanceof MaterialInstance ? $instance->toArray() : $instance;
        }

        return [
            "geometryDescription" => is_array($this->geometry) ? $this->geometry : ["identifier" => $this->geometry],
            "materialInstancesDescription" => [
                "mappings" => CompoundTag::create(),
                "materials" => $materials
            ]
        ];
    }
}
