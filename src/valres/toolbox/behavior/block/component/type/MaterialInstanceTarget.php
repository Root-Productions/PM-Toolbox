<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum MaterialInstanceTarget: string {
    case NORTH = "north";
    case SOUTH = "south";
    case EAST = "east";
    case WEST = "west";

    case UP = "up";
    case DOWN = "down";
    case ALL = "*";

    public function getName(): string {
        return $this->name;
    }

    public function getValue(): string {
        return $this->value;
    }
}