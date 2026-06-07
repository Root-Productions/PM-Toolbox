<?php

declare(strict_types=1);

namespace valres\toolbox\manager\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
final readonly class AutoRegister {
    public function __construct(
        public AutoRegisterType $type
    ) {
    }
}