<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use Throwable;
use valres\toolbox\manager\exception\ManagerLifecycleException;

final class ManagerLifecycleRunner {
    public function __construct(
        private readonly ManagerLogger $logger
    ) {
    }

    /** @throws ManagerLifecycleException */
    public function load(BaseManager $manager): void {
        $start = microtime(true);

        try {
            $manager->onLoad();
            $this->logger->info("Loaded {$manager->getName()} in {$this->formatTime($start)}ms.");
        } catch (Throwable $e) {
            $this->logger->error("Error loading {$manager->getName()} ({$this->formatTime($start)}ms): " . $this->logger->sanitizeError($e->getMessage()));
            throw ManagerLifecycleException::loadFailed($manager->getName(), $e);
        }
    }

    /** @throws ManagerLifecycleException */
    public function init(BaseManager $manager): float {
        $start = microtime(true);

        try {
            $manager->init();
            $manager->setEnabled();

            return (float) $this->formatTime($start);
        } catch (Throwable $e) {
            $this->logger->error("Error initializing {$manager->getName()} ({$this->formatTime($start)}ms): " . $this->logger->sanitizeError($e->getMessage()));
            throw ManagerLifecycleException::initFailed($manager->getName(), $e);
        }
    }

    /** @throws ManagerLifecycleException */
    public function save(BaseManager $manager): void {
        try {
            if ($manager->isEnabled()) {
                $manager->save();
            }
        } catch (Throwable $e) {
            $this->logger->error("Error saving {$manager->getName()}: " . $this->logger->sanitizeError($e->getMessage()));
            throw ManagerLifecycleException::saveFailed($manager->getName(), $e);
        }
    }

    public function formatTime(float $start): string {
        return number_format((microtime(true) - $start) * 1000, 2, ".", "");
    }
}
