<?php

declare(strict_types=1);

namespace valres\toolbox\discord\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DiscordMessage {
    public function __construct(
        public readonly ?string $content = null,
        public readonly ?string $username = null,
        public readonly ?string $avatarUrl = null
    ) {
    }
}
