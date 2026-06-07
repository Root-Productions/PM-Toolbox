<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

use Exception;
use Throwable;

class ManagerException extends Exception {
    public static function fromMessage(string $message, ?Throwable $previous = null): self {
        return new self($message, 0, $previous);
    }
}