<?php

declare(strict_types=1);

namespace valres\toolbox\manager\exception;

use Throwable;

final class ManagerLifecycleException extends ManagerException {
    public static function loadFailed(string $managerName, Throwable $previous): self {
        return new self("Failed to load {$managerName}.", 0, $previous);
    }

    public static function initFailed(string $managerName, Throwable $previous): self {
        return new self("Failed to initialize {$managerName}.", 0, $previous);
    }

    public static function saveFailed(string $managerName, Throwable $previous): self {
        return new self("Failed to save {$managerName}.", 0, $previous);
    }
}
