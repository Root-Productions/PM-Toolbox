<?php

declare(strict_types=1);

namespace valres\toolbox\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class AutoLoadCancel {
}