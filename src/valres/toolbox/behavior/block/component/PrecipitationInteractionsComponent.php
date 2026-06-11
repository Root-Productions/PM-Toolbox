<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\PrecipitationBehavior;

final class PrecipitationInteractionsComponent extends BlockComponent {
    public function __construct(private readonly PrecipitationBehavior|string $behavior = PrecipitationBehavior::OBSTRUCT_RAIN_ACCUMULATE_SNOW) {
    }

    public static function identifier(): string {
        return "minecraft:precipitation_interactions";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "precipitation_behavior" => $this->behavior instanceof PrecipitationBehavior ? $this->behavior->value : $this->behavior
        ]);
    }
}
