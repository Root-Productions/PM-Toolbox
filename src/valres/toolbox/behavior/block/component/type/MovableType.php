<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum MovableType: string {
    case IMMOVABLE = "immovable";
    case POPPED = "popped";
    case PUSH = "push";
    case PUSH_PULL = "push_pull";
}
