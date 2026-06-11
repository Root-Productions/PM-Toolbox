<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

final class DestructibleByMiningComponent extends BlockComponent {
    public function __construct(private readonly bool|float|int $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:destructible_by_mining";
    }

    public static function seconds(float|int $secondsToDestroy): self {
        return new self($secondsToDestroy);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag(is_bool($this->value) ? $this->value : [
            "seconds_to_destroy" => $this->value
        ]);
    }
}
