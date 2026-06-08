<?php

declare(strict_types=1);

namespace valres\toolbox\command\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CommandPermission {
    public function __construct(
        public readonly ?string $permission = null,
        public readonly bool $isOp = true
    ) {
    }
}
