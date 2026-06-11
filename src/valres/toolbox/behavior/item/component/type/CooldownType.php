<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

enum CooldownType: string {
    case USE = "use";
    case ATTACK = "attack";
}
