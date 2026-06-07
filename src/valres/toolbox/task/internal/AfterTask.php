<?php

declare(strict_types=1);

namespace valres\toolbox\task\internal;

use Closure;
use pocketmine\scheduler\Task;
use valres\toolbox\task\TaskHandle;

final class AfterTask extends Task {
    public function __construct(
        private readonly TaskHandle $handle,
        private readonly Closure $callback
    ) {
    }

    public function onRun(): void {
        if ($this->handle->isCancelled()) {
            return;
        }

        ($this->callback)();
        $this->handle->cancel();
    }
}