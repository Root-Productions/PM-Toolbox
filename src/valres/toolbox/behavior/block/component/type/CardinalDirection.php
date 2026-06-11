<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum CardinalDirection: string {
    case NORTH = "north";
    case EAST = "east";
    case SOUTH = "south";
    case WEST = "west";
}
