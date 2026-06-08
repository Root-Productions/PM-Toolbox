<?php

declare(strict_types=1);

namespace valres\toolbox\packet\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class IncomingPacket {
    /** @param class-string[]|class-string|null $packets */
    public function __construct(
        public array|string|null $packets = null
    ) {
    }
}
