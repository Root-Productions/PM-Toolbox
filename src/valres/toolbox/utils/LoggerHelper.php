<?php

declare(strict_types=1);

namespace valres\toolbox\utils;

use valres\toolbox\ToolboxLoader;

final class LoggerHelper {
    public static function printSummary(array $counts): void {
        $loader = ToolboxLoader::getLoader();
        $logger = $loader->getLogger();

        $logger->info("§8---------------------------");
        $logger->info("§7 Loading finished for §9" . $loader->getName());
        foreach ($counts as $type => $count) {
            $label = match ($type) {
                "command" => "Commands",
                "listener" => "Listeners",
                default => "Custom"
            };
            $logger->info("§7 » §f{$count} §7{$label}");
        }
        $logger->info("§8----------------§7 by Root §8--");
    }
}