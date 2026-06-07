<?php

declare(strict_types=1);

namespace valres\toolbox\rcon\exception;

use Socket;

class RconSocketException extends RconException {
    public static function fromLastError(string $message, ?Socket $socket = null): self {
        $error = $socket !== null ? socket_last_error($socket) : socket_last_error();
        return new self($message . ": " . trim(socket_strerror($error)), $error);
    }
}
