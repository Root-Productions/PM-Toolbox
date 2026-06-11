<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum ConnectionRuleMode: string {
    case ALL = "all";
    case ONLY_FENCES = "only_fences";
    case NONE = "none";
}
