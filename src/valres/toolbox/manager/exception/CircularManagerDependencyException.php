<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

final class CircularManagerDependencyException extends ManagerException {
    /** @param string[] $managerNames */
    public function __construct(array $managerNames) {
        parent::__construct("Circular manager dependencies detected: " . implode(", ", $managerNames));
    }
}
