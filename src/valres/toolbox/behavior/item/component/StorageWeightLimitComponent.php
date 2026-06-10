<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class StorageWeightLimitComponent extends DataDrivenItemComponent {
    public function __construct(private readonly int $maxWeightLimit) {
    }

    public static function identifier(): string {
        return "minecraft:storage_weight_limit";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["max_weight_limit" => $this->maxWeightLimit]);
    }
}
