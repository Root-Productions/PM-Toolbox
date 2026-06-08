<?php

declare(strict_types=1);

namespace valres\toolbox\utils;

use pocketmine\command\Command;
use pocketmine\event\Listener;
use ReflectionException;
use RuntimeException;
use valres\toolbox\ToolboxLoader;

final class AutoLoad {
    private static bool $initialized = false;

    private static LoaderRegistry $registry;

    public static function init(bool $printSummary = true): void {
        if (self::$initialized) {
            throw new RuntimeException("AutoLoad is already initialized.");
        }

        self::$initialized = true;
        self::$registry = new LoaderRegistry($printSummary);
    }

    private static function ensureInit(): void {
        if (!self::$initialized) {
            throw new RuntimeException("AutoLoad must be initialized before use.");
        }
    }

    public static function isInitialized(): bool {
        return self::$initialized;
    }

    /** @throws ReflectionException */
    public static function commands(string $directory): void {
        self::ensureInit();
        self::$registry->autoload(
            $directory,
            Command::class,
            fn (Command $cmd) => ToolboxLoader::registerCommand($cmd),
            "command"
        );
    }

    /** @throws ReflectionException */
    public static function listeners(string $directory): void {
        self::ensureInit();
        self::$registry->autoload(
            $directory,
            Listener::class,
            fn (Listener $listener) => ToolboxLoader::getLoader()->getServer()->getPluginManager()->registerEvents($listener, ToolboxLoader::getLoader()),
            "listener"
        );
    }

    /** @throws ReflectionException */
    public static function custom(string $directory, string $requiredType, callable $callback): void {
        self::ensureInit();
        self::$registry->autoload($directory, $requiredType, $callback, "custom");
    }

    public static function printSummary(): void {
        self::ensureInit();
        self::$registry->tryPrintSummary();
    }
}
