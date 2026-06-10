<?php

declare(strict_types=1);

namespace valres\toolbox\discord\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class DiscordField {
    public function __construct(
        public readonly ?string $name = null,
        public readonly bool $inline = false,
        public readonly bool $skipNull = true
    ) {
    }
}
