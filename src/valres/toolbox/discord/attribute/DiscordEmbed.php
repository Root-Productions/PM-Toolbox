<?php

declare(strict_types=1);

namespace valres\toolbox\discord\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DiscordEmbed {
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $color = null,
        public readonly ?string $footer = null,
        public readonly ?string $footerIconUrl = null,
        public readonly bool $timestamp = false
    ) {
    }
}
