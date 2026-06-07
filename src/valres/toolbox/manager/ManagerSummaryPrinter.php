<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

final class ManagerSummaryPrinter {
    public function __construct(
        private readonly ManagerLogger $logger
    ) {
    }

    /** @param array<string, array{version: string, commands: int, listeners: int, time: float, summary: ?array}> $summary */
    public function print(array $summary): void {
        $count = count($summary);
        $this->logger->info("Managers found: {$count}");

        if ($count === 0) {
            $this->logger->info("No manager loaded.");
            return;
        }

        $maxVersionLength = 0;
        $maxNameLength = 0;
        $maxTimeLength = 0;

        foreach ($summary as $name => $data) {
            $maxVersionLength = max($maxVersionLength, mb_strlen($data["version"]));
            $maxNameLength = max($maxNameLength, mb_strlen($name));
            $maxTimeLength = max($maxTimeLength, mb_strlen((string) $data["time"]));
        }

        $this->logger->info("Loaded managers:");

        foreach ($summary as $name => $data) {
            $version = str_pad($data["version"], $maxVersionLength);
            $namePad = str_pad($name, $maxNameLength);
            $time = str_pad((string) $data["time"], $maxTimeLength, " ", STR_PAD_LEFT);

            $this->logger->info("- (v{$version}) {$namePad} ({$time}ms)");
            $this->printDetails($data["commands"], $data["listeners"], $data["summary"] ?? []);
        }
    }

    /** @param string[] $summaryLines */
    private function printDetails(int $commandsCount, int $listenersCount, array $summaryLines): void {
        if ($commandsCount === 0 && $listenersCount === 0 && empty($summaryLines)) {
            $this->logger->info("  - No file found");
            return;
        }

        if ($commandsCount > 0) {
            $this->logger->info("  - command/ ({$commandsCount})");
        }

        if ($listenersCount > 0) {
            $this->logger->info("  - listener/ ({$listenersCount})");
        }

        foreach ($summaryLines as $line) {
            $this->logger->info("  - {$line}");
        }
    }
}
