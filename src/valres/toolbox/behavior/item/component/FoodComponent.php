<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class FoodComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly int $nutrition,
        private readonly float $saturationModifier,
        private readonly ?bool $canAlwaysEat = null,
        private readonly ?string $usingConvertsTo = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:food";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "nutrition" => $this->nutrition,
            "saturation_modifier" => $this->saturationModifier,
            "can_always_eat" => $this->canAlwaysEat,
            "using_converts_to" => $this->usingConvertsTo
        ]);
    }
}
