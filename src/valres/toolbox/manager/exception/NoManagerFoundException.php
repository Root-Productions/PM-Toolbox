<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

final class NoManagerFoundException extends ManagerException {
    public function __construct(string $directory) {
        parent::__construct("No managers found in directory {$directory}.");
    }
}
