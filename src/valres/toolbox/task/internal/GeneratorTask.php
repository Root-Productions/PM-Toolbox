<?php

declare(strict_types=1);

namespace valres\toolbox\task\internal;

use Closure;
use Generator;
use pocketmine\scheduler\Task;
use valres\toolbox\task\TaskHandle;

final class GeneratorTask extends Task {
    private bool $started = false;

    public function __construct(
        private readonly TaskHandle $handle,
        private readonly Generator $generator,
        private readonly int $stepsPerTick,
        private readonly ?Closure $onComplete = null
    ) {
    }

    public function onRun(): void {
        if ($this->handle->isCancelled()) {
            $this->getHandler()?->cancel();
            return;
        }

        for ($i = 0; $i < $this->stepsPerTick; ++$i) {
            if (!$this->started) {
                $this->started = true;
                if (!$this->generator->valid()) {
                    $this->complete();
                    return;
                }
            } else {
                $this->generator->next();
                if (!$this->generator->valid()) {
                    $this->complete();
                    return;
                }
            }
        }
    }

    private function complete(): void {
        $this->handle->cancel();
        $this->getHandler()?->cancel();
        if ($this->onComplete !== null) {
            ($this->onComplete)();
        }
    }
}
