<?php

declare(strict_types=1);

namespace valres\toolbox\manager\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ManagerDependsOn {
    /** @param string[] $dependencies */
    public function __construct(public array $dependencies) {
    }
}