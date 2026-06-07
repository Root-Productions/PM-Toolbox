<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use pocketmine\event\EventPriority;
use pocketmine\plugin\PluginBase;

final class Packets {
    public static function createMonitor(PluginBase $plugin): PacketMonitor {
        return new PacketMonitor($plugin);
    }

    public static function createInterceptor(
        PluginBase $plugin,
        int $priority = EventPriority::NORMAL,
        bool $handleCancelled = false
    ): PacketInterceptor {
        return new PacketInterceptor($plugin, $priority, $handleCancelled);
    }
}
