<?php

declare(strict_types=1);

namespace valres\toolbox\task;

use Closure;
use valres\toolbox\task\internal\AfterTask;
use valres\toolbox\task\internal\EveryTask;
use valres\toolbox\task\internal\RepeatTask;
use valres\toolbox\ToolboxLoader;

final class Tasks {
    public static function after(Closure $callback, int $delayTicks): TaskHandle {
        $handle = new TaskHandle();
        ToolboxLoader::getLoader()->getScheduler()->scheduleDelayedTask(new AfterTask($handle, $callback), max(0, $delayTicks));
        return $handle;
    }

    public static function every(Closure $callback, int $intervalTicks): TaskHandle {
        $handle = new TaskHandle();
        ToolBoxLoader::getLoader()->getScheduler()->scheduleRepeatingTask(new EveryTask($handle, $callback), max(1, $intervalTicks));
        return $handle;
    }

    public static function repeat(Closure $callback, int $times, int $intervalTicks): TaskHandle {
        $handle = new TaskHandle();
        if ($times <= 0) {
            $handle->cancel();
            return $handle;
        }

        ToolBoxLoader::getLoader()->getScheduler()->scheduleRepeatingTask(new RepeatTask($handle, $times, $callback), max(1, $intervalTicks));
        return $handle;
    }
}