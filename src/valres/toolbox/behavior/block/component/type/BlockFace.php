<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum BlockFace: string {
    case ALL = "all";
    case SIDE = "side";
    case DOWN = "down";
    case UP = "up";
    case NORTH = "north";
    case SOUTH = "south";
    case WEST = "west";
    case EAST = "east";
}
