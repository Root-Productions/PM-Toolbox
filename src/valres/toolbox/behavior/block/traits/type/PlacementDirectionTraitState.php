<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits\type;

enum PlacementDirectionTraitState: string {
    case CARDINAL_DIRECTION = "minecraft:cardinal_direction";
    case FACING_DIRECTION = "minecraft:facing_direction";
    case CORNER_AND_CARDINAL_DIRECTION = "minecraft:corner_and_cardinal_direction";
}
