<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class RedstoneConductivityComponent extends BlockComponent {
    public function __construct(
        private readonly ?bool $redstoneConductor = null,
        private readonly ?bool $allowsWireToStepDown = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:redstone_conductivity";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "redstone_conductor" => $this->redstoneConductor,
            "allows_wire_to_step_down" => $this->allowsWireToStepDown
        ]);
    }
}
