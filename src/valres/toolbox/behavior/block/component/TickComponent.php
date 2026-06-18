<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Schedules block tick events within the configured interval range. */
final class TickComponent extends BlockComponent {
    public function __construct(
        private readonly int $minInterval,
        private readonly int $maxInterval,
        private readonly ?bool $looping = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:tick";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "interval_range" => [$this->minInterval, $this->maxInterval],
            "looping" => $this->looping
        ]);
    }
}
