<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum LiquidTouchReaction: string {
    case BLOCKING = "blocking";
    case BROKEN = "broken";
    case NO_REACTION = "no_reaction";
    case POPPED = "popped";
}
