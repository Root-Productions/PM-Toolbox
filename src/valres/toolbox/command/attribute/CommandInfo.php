<?php

declare(strict_types=1);

namespace valres\toolbox\command\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CommandInfo {
    public function __construct(
        public readonly string $name,
        public readonly string $description = "",
        public readonly array $aliases = [],
        public readonly ?string $permission = null
    ) {
    }
}
