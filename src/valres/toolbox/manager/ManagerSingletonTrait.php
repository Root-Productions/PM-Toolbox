<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use valres\toolbox\manager\exception\ManagerNotInitializedException;
use InvalidArgumentException;

trait ManagerSingletonTrait {
    /** @var array<class-string, BaseManager> */
    private static array $managerInstances = [];

    /**
     * @throws ManagerNotInitializedException
     */
    public static function getInstance(): static {
        $class = static::class;

        if (!isset(self::$managerInstances[$class]) || !self::$managerInstances[$class] instanceof static) {
            throw new ManagerNotInitializedException($class);
        }

        return self::$managerInstances[$class];
    }

    public static function hasInstance(): bool {
        $class = static::class;

        return isset(self::$managerInstances[$class]) && self::$managerInstances[$class] instanceof static;
    }

    public static function setInstance(BaseManager $instance): void {
        if (!$instance instanceof static) {
            throw new InvalidArgumentException("Manager singleton instance must be an instance of " . static::class . ".");
        }

        self::$managerInstances[static::class] = $instance;
    }

    public static function resetInstance(): void {
        unset(self::$managerInstances[static::class]);
    }

    final protected function registerManagerInstance(): void {
        self::$managerInstances[static::class] = $this;
    }

    final public function unregisterManagerInstance(): void {
        unset(self::$managerInstances[static::class]);
    }
}
