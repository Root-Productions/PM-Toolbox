<?php

declare(strict_types=1);

namespace valres\toolbox\task;

final class TaskHandle {
    private bool $cancelled = false;

    public function cancel(): void {
        $this->cancelled = true;
    }

    public function isCancelled(): bool {
        return $this->cancelled;
    }
}