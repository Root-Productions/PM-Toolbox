<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Defines the redstone power required by the block and its propagation behavior. */
final class RedstoneConsumerComponent extends BlockComponent {
    public function __construct(
        private readonly int $minPower,
        private readonly ?bool $propagatesPower = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:redstone_consumer";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "min_power" => $this->minPower,
            "propagates_power" => $this->propagatesPower
        ]);
    }
}
