<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

enum PacketDirection {
    case INCOMING;
    case OUTGOING;
}
