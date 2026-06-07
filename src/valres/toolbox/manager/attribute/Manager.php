<?php

declare(strict_types=1);

namespace valres\toolbox\manager\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Manager {
    public function __construct(
        public ?string $name = null,
        public string $version = "BETA"
    ) {
    }
}