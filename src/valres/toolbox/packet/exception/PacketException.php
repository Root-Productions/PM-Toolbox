<?php

declare(strict_types=1);

namespace valres\toolbox\packet\exception;

use Exception;
use Throwable;

class PacketException extends Exception {
    public static function fromMessage(string $message, ?Throwable $previous = null): self {
        return new self($message, 0, $previous);
    }
}
