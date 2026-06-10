<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines block destroy speeds and efficiency usage for tool-like items. */
final class DiggerComponent extends DataDrivenItemComponent {
    /** @param array<int, array{block: string|array, speed: int}> $destroySpeeds */
    public function __construct(
        private readonly array $destroySpeeds,
        private readonly ?bool $useEfficiency = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:digger";
    }

    public static function destroySpeed(string|array $block, int $speed): array {
        return [
            "block" => $block,
            "speed" => $speed
        ];
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "use_efficiency" => $this->useEfficiency,
            "destroy_speeds" => $this->destroySpeeds
        ]);
    }
}
