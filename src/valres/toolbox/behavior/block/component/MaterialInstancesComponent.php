<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\MaterialInstance;
use valres\toolbox\behavior\block\component\type\MaterialInstanceTarget;
use valres\toolbox\behavior\block\component\type\RenderMethod;

/** Maps block faces or geometry bones to textures and render settings. */
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

    public static function sided(
        string $side,
        ?string $top = null,
        ?string $bottom = null,
        RenderMethod|string $renderMethod = RenderMethod::OPAQUE
    ): self {
        $top ??= $side;
        $bottom ??= $side;

        return new self([
            MaterialInstanceTarget::NORTH->value => new MaterialInstance($side, $renderMethod),
            MaterialInstanceTarget::EAST->value => new MaterialInstance($side, $renderMethod),
            MaterialInstanceTarget::SOUTH->value => new MaterialInstance($side, $renderMethod),
            MaterialInstanceTarget::WEST->value => new MaterialInstance($side, $renderMethod),
            MaterialInstanceTarget::UP->value => new MaterialInstance($top, $renderMethod),
            MaterialInstanceTarget::DOWN->value => new MaterialInstance($bottom, $renderMethod)
        ]);
    }

    public function toNBT(): Tag {
        $instances = [];
        foreach ($this->instances as $name => $instance) {
            $instances[$name] = $instance instanceof MaterialInstance ? $instance->toArray() : $instance;
        }

        return CompoundTag::create()
            ->setTag("mappings", CompoundTag::create())
            ->setTag("materials", ComponentNbtHelper::compound($instances));
    }
}
