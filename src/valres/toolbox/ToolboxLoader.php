<?php

declare(strict_types=1);

namespace valres\toolbox;

use pocketmine\event\EventPriority;
use pocketmine\plugin\PluginBase;
use valres\toolbox\command\CommandInterceptor;
use valres\toolbox\manager\BaseManager;
use valres\toolbox\manager\exception\ManagerException;
use valres\toolbox\manager\ManagerHandler;
use valres\toolbox\packet\exception\InvalidPacketHandlerException;
use valres\toolbox\packet\PacketInterceptor;
use valres\toolbox\packet\PacketMonitor;
use valres\toolbox\packet\Packets;
use valres\toolbox\rcon\exception\RconException;
use valres\toolbox\rcon\RconCommandSender;
use valres\toolbox\rcon\RconInterface;
use valres\toolbox\rcon\RconSettings;

class ToolboxLoader {
    private static PluginBase $loader;

    private static ?ManagerHandler $managerHandler = null;

    private static bool $loaded = false;
    private static bool $enabled = false;
    private static bool $managersLoaded = false;
    private static bool $managersEnabled = false;

    /**
     * @return PluginBase
     */
    public static function getLoader(): PluginBase {
        return self::$loader;
    }

    /** @throws ManagerException */
    public static function load(PluginBase $loader, string $managersDirectory = "manager", bool $loadManagers = true): void {
        if (!self::$loaded) {
            self::$loaded = true;
            self::$loader = $loader;
        }

        if ($loadManagers) {
            self::loadManagers($managersDirectory);
        }
    }

    /** @throws ManagerException|InvalidPacketHandlerException */
    public static function enable(PluginBase $loader, ?RconSettings $rconSettings = null, bool $enableManagers = true): void {
        if (self::$enabled) {
            return;
        }

        self::$enabled = true;

        if (!self::$loaded) {
            self::load($loader);
        }

        if ($enableManagers) {
            self::enableManagers();
        }

        if ($rconSettings !== null) {
            self::startRCON($rconSettings);
        }

        Packets::createInterceptor($loader, EventPriority::HIGHEST)
            ->registerOutgoing(new CommandInterceptor());
    }

    public static function getManagerHandler(): ManagerHandler {
        return self::$managerHandler ??= new ManagerHandler();
    }

    /** @throws ManagerException */
    public static function loadManagers(string $managersDirectory = "manager"): ManagerHandler {
        $handler = self::getManagerHandler();

        if (!self::$managersLoaded) {
            $handler->load($managersDirectory);
            self::$managersLoaded = true;
        }

        return $handler;
    }

    /** @throws ManagerException */
    public static function enableManagers(?string $managersDirectory = null): ManagerHandler {
        if ($managersDirectory !== null) {
            self::loadManagers($managersDirectory);
        } elseif (!self::$managersLoaded) {
            self::loadManagers();
        }

        $handler = self::getManagerHandler();

        if (!self::$managersEnabled) {
            $handler->enable();
            self::$managersEnabled = true;
        }

        return $handler;
    }

    public static function disableManagers(): void {
        if (self::$managerHandler === null || !self::$managersEnabled) {
            return;
        }

        self::$managerHandler->disable();
        self::$managersEnabled = false;
    }

    public static function disable(bool $disableManagers = true): void {
        if ($disableManagers) {
            self::disableManagers();
        }

        self::$enabled = false;
    }

    public static function getManager(string $name): ?BaseManager {
        return self::getManagerHandler()->getManager($name);
    }

    public static function getManagerOf(string $class): ?BaseManager {
        return self::getManagerHandler()->getManagerOf($class);
    }

    public static function hasManager(string $name): bool {
        return self::getManagerHandler()->hasManager($name);
    }

    public static function createPacketMonitor(?PluginBase $plugin = null): PacketMonitor {
        return Packets::createMonitor($plugin ?? self::$loader);
    }

    public static function createPacketInterceptor(?PluginBase $plugin = null, int $priority = EventPriority::NORMAL, bool $handleCancelled = false): PacketInterceptor {
        return Packets::createInterceptor($plugin ?? self::$loader, $priority, $handleCancelled);
    }

    public static function startRCON(RconSettings $settings): void {
        $loader = self::$loader;

        try {
            $loader->getServer()->getNetwork()->registerInterface(new RconInterface(
                $settings,
                function(string $commandLine) use ($loader): string {
                    $sender = new RconCommandSender($loader->getServer(), $loader->getServer()->getLanguage());
                    $sender->recalculatePermissions();
                    $loader->getServer()->dispatchCommand($sender, $commandLine);

                    return $sender->getMessage();
                },
                $loader->getServer()->getLogger(),
                $loader->getServer()->getTickSleeper()
            ));

            $loader->getLogger()->info("Starting RCON on " . $settings->getAddress() . ":" . $settings->getPort());
        } catch (RconException $e) {
            $loader->getLogger()->alert("Failed to start RCON: " . $e->getMessage());
            $loader->getLogger()->logException($e);
            $loader->getServer()->getPluginManager()->disablePlugin($loader);
        }
    }
}
