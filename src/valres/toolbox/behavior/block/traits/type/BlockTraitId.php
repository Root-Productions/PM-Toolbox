<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits\type;

enum BlockTraitId: string {
    case CONNECTION = "minecraft:connection";
    case MULTI_BLOCK = "minecraft:multi_block";
    case PLACEMENT_DIRECTION = "minecraft:placement_direction";
    case PLACEMENT_POSITION = "minecraft:placement_position";
}
