<?php

declare(strict_types=1);

namespace valres\toolbox\packet\attribute;

use Attribute;
use valres\toolbox\packet\PacketDirection;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class PacketHandler {
    /** @param class-string[]|class-string|null $packets */
    public function __construct(
        public PacketDirection $direction,
        public array|string|null $packets = null
    ) {
    }
}
