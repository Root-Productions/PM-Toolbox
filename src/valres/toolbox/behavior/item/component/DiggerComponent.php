<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\BlockDescriptor;
use valres\toolbox\behavior\item\component\type\DestroySpeed;

/** Defines block destroy speeds and efficiency usage for tool-like items. */
final class DiggerComponent extends DataDrivenItemComponent {
    /** @param array<int, DestroySpeed|array{block: string|array, speed: int}> $destroySpeeds */
    public function __construct(
        private readonly array $destroySpeeds,
        private readonly ?bool $useEfficiency = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:digger";
    }

    public static function destroySpeed(BlockDescriptor|string|array $block, int $speed): DestroySpeed {
        return new DestroySpeed($block, $speed);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "use_efficiency" => $this->useEfficiency,
            "destroy_speeds" => $this->destroySpeeds
        ]);
    }
}
