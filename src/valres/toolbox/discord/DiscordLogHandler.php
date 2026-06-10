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

    /**
     * Starts the global Discord queue tick handler.
     *
     * @param  Plugin $plugin
     * @param  int    $intervalTicks
     *
     * @throws DiscordAlreadyRegisteredException
     * @return void
     */
    public static function register(Plugin $plugin, int $intervalTicks = 20): void {
        if (self::isRegistered()) {
            throw new DiscordAlreadyRegisteredException("DiscordLogHandler is already registered.");
        }

        self::$registrant = $plugin;
        self::$taskHandle = Tasks::every(static function(): void {
            DiscordQueueManager::getInstance()->tick();
        }, max(1, $intervalTicks));
    }

    /**
     * Checks whether the Discord queue tick handler is running.
     *
     * @return bool
     */
    public static function isRegistered(): bool {
        return self::$registrant !== null;
    }

    /**
     * Returns the plugin that registered the global Discord queue tick handler.
     *
     * @return Plugin|null
     */
    public static function getRegistrant(): ?Plugin {
        return self::$registrant;
    }

    /**
     * Stops the Discord queue tick handler and clears queued payloads.
     *
     * @return void
     */
    public static function unregister(): void {
        self::$taskHandle?->cancel();
        self::$taskHandle = null;
        self::$registrant = null;
        DiscordQueueManager::getInstance()->clear();
    }
}
