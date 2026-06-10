<?php

declare(strict_types=1);

namespace valres\toolbox\discord;

use pocketmine\plugin\Plugin;
use valres\toolbox\discord\exception\DiscordAlreadyRegisteredException;
use valres\toolbox\discord\queue\DiscordQueueManager;
use valres\toolbox\task\TaskHandle;
use valres\toolbox\task\Tasks;

final class DiscordLogHandler {
    private static ?Plugin $registrant = null;
    private static ?TaskHandle $taskHandle = null;

    public static function register(Plugin $plugin, int $intervalTicks = 20): void {
        if (self::isRegistered()) {
            throw new DiscordAlreadyRegisteredException("DiscordLogHandler is already registered.");
        }

        self::$registrant = $plugin;
        self::$taskHandle = Tasks::every(static function(): void {
            DiscordQueueManager::getInstance()->tick();
        }, max(1, $intervalTicks));
    }

    public static function isRegistered(): bool {
        return self::$registrant !== null;
    }

    public static function getRegistrant(): ?Plugin {
        return self::$registrant;
    }

    public static function unregister(): void {
        self::$taskHandle?->cancel();
        self::$taskHandle = null;
        self::$registrant = null;
        DiscordQueueManager::getInstance()->clear();
    }
}
