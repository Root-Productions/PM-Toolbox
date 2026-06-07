<?php

declare(strict_types=1);

namespace valres\toolbox\manager\attribute;

enum AutoRegisterType: string {
    case COMMANDS = 'command';
    case LISTENERS = 'listener';
}