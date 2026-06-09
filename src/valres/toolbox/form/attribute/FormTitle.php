<?php

declare(strict_types=1);

namespace valres\toolbox\form\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class FormTitle {
    public function __construct(public readonly string $title) {
    }
}
