<?php

declare(strict_types=1);

namespace valres\toolbox\task\internal;

use Closure;
use pocketmine\scheduler\Task;
use valres\toolbox\task\TaskHandle;

final class EveryTask extends Task {
    private int $count = 0;

    public function __construct(
        private readonly TaskHandle $handle,
        private readonly Closure $callback
    ) {
    }

    public function onRun(): void {
        if ($this->handle->isCancelled()) {
            $this->getHandler()?->cancel();
            return;
        }

        $this->count++;
        ($this->callback)($this->count);
    }
}