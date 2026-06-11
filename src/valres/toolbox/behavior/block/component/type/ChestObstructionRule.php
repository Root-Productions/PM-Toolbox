<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum ChestObstructionRule: string {
    case ALWAYS = "always";
    case NEVER = "never";
    case SHAPE = "shape";
}
