<?php

declare(strict_types=1);

namespace valres\toolbox\manager\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class AutoRegisterAll {
}
