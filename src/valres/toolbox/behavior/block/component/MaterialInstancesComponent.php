<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\MaterialInstance;

final class MaterialInstancesComponent extends BlockComponent {
    /** @param array<string, MaterialInstance|string|array<string, mixed>> $instances */
    public function __construct(private readonly array $instances) {
    }

    public static function identifier(): string {
        return "minecraft:material_instances";
    }

    public static function all(MaterialInstance $instance): self {
        return new self(["*" => $instance]);
    }

    public function toNBT(): Tag {
        $instances = [];
        foreach ($this->instances as $name => $instance) {
            $instances[$name] = $instance instanceof MaterialInstance ? $instance->toArray() : $instance;
        }

        return ComponentNbtHelper::tag($instances);
    }
}
