<?php

declare(strict_types=1);

namespace valres\toolbox\manager\attribute;

enum ManagerPriorityEnum: int {
    case PRIORITY_CRITICAL = 100;
    case PRIORITY_HIGH = 80;
    case PRIORITY_MEDIUM = 60;
    case PRIORITY_LOW = 40;
    case PRIORITY_COMMANDS = 20;
    case PRIORITY_FINALIZATION = 10;
}
