<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

final class DuplicateManagerException extends ManagerException {
    public function __construct(string $name) {
        parent::__construct("Duplicate manager name detected: '{$name}'.");
    }
}
