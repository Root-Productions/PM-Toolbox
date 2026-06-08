<?php

declare(strict_types=1);

namespace valres\toolbox\command\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class CommandArgument {
    /**
     * @param class-string $type Argument implementation class name.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $optional = false,
        public readonly mixed $default = null
    ) {
    }
}
