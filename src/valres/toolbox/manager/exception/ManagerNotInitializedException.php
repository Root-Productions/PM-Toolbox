<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

final class ManagerNotInitializedException extends ManagerException {
    public function __construct(string $class) {
        parent::__construct("Manager singleton is not initialized for {$class}.");
    }
}
