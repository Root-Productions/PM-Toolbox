<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

final class MissingManagerDependencyException extends ManagerException {
    /** @param string[] $dependencies */
    public function __construct(string $managerName, array $dependencies) {
        parent::__construct("Manager '{$managerName}' depends on missing manager(s): " . implode(", ", $dependencies));
    }
}
