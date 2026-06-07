<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use pocketmine\utils\SingletonTrait;
use Throwable;
use valres\toolbox\manager\exception\ManagerException;
use valres\toolbox\manager\exception\ManagerLifecycleException;
use valres\toolbox\ToolboxLoader;

final class ManagerHandler {
    use SingletonTrait;

    /** @var array<string, BaseManager> */
    private array $managers = [];

    /** @var BaseManager[] */
    private array $sortedManagers = [];

    /** @var array<string, array{version: string, commands: int, listeners: int, time: float, summary: ?array}> */
    private array $modulesSummary = [];

    private string $managersDirectory = "manager";

    private bool $loaded = false;

    private bool $enabled = false;

    private bool $shutdownRegistered = false;

    private bool $shutdownPerformed = false;

    private ManagerLogger $logger;

    private ManagerDiscovery $discovery;

    private ManagerDependencyResolver $dependencyResolver;

    private ManagerLifecycleRunner $lifecycleRunner;

    private ManagerComponentRegistrar $componentRegistrar;

    private ManagerSummaryPrinter $summaryPrinter;

    public function __construct() {
        $this->logger = new ManagerLogger();
        $this->discovery = new ManagerDiscovery();
        $this->dependencyResolver = new ManagerDependencyResolver();
        $this->lifecycleRunner = new ManagerLifecycleRunner($this->logger);
        $this->componentRegistrar = new ManagerComponentRegistrar($this->logger);
        $this->summaryPrinter = new ManagerSummaryPrinter($this->logger);
    }

    /** @throws ManagerException */
    public function load(string $managersDirectory): void {
        self::setInstance($this);

        $this->managersDirectory = $managersDirectory;
        $globalStart = microtime(true);

        try {
            $managers = $this->discovery->discover($managersDirectory);
            $this->sortedManagers = $this->dependencyResolver->resolve($managers);

            foreach ($this->sortedManagers as $manager) {
                $this->lifecycleRunner->load($manager);
            }

            $this->loaded = true;
            $this->logger->info("Managers discovered and loaded in {$this->lifecycleRunner->formatTime($globalStart)}ms.");
        } catch (Throwable $e) {
            $message = "Failed to load managers: " . $this->logger->sanitizeError($e->getMessage());
            $this->logger->error($message);
            throw new ManagerException($message, 0, $e);
        }
    }

    /** @throws ManagerException */
    public function enable(): void {
        if (!$this->loaded) {
            $this->load($this->managersDirectory);
        }

        if ($this->enabled) {
            return;
        }

        $this->enabled = true;
        $this->shutdownPerformed = false;

        if (!$this->shutdownRegistered) {
            $this->registerShutdownHandlers();
            $this->shutdownRegistered = true;
        }

        $globalStart = microtime(true);

        try {
            $this->initializeManagers();
            $this->summaryPrinter->print($this->modulesSummary);
            $this->logger->info("All managers enabled successfully in {$this->lifecycleRunner->formatTime($globalStart)}ms.");
        } catch (Throwable $e) {
            $message = "Failed to enable managers: " . $this->logger->sanitizeError($e->getMessage());
            $this->logger->error($message);
            throw new ManagerException($message, 0, $e);
        }
    }

    /** @throws ManagerException */
    private function initializeManagers(): void {
        foreach ($this->sortedManagers as $manager) {
            $time = $this->lifecycleRunner->init($manager);
            $this->managers[$manager->getName()] = $manager;
            $this->modulesSummary[$manager->getName()] = $this->componentRegistrar->register($manager, $time);
        }
    }

    public function performShutdown(string $trigger): void {
        if ($this->shutdownPerformed) {
            return;
        }

        $this->shutdownPerformed = true;
        $this->logger->info("Shutdown triggered by: {$trigger}");

        foreach (array_reverse($this->managers) as $manager) {
            try {
                $this->lifecycleRunner->save($manager);
            } catch (ManagerLifecycleException $e) {
                ToolboxLoader::getLoader()->getLogger()->debug($e->getTraceAsString());
            }
        }

        $this->logger->info("All managers have been safely saved and unloaded.");
    }

    public function registerShutdownHandlers(): void {
        register_shutdown_function(fn () => $this->performShutdown("shutdown_function"));

        if (function_exists("pcntl_signal")) {
            $handler = function (int $signal): void {
                $this->performShutdown("signal_{$signal}");
                exit(0);
            };

            foreach ([SIGTERM, SIGINT, SIGHUP] as $signal) {
                pcntl_signal($signal, $handler);
            }
        }

        set_exception_handler(fn (Throwable $e) => $this->performShutdown("exception_handler"));
    }

    public function disable(): void {
        $this->performShutdown("manual_disable");

        foreach ($this->managers as $manager) {
            $manager->setEnabled(false);
            $manager->unregisterManagerInstance();
        }

        $this->managers = [];
        $this->modulesSummary = [];
        $this->enabled = false;
    }

    public function getManager(string $name): ?BaseManager {
        return $this->managers[$name] ?? null;
    }

    public function getManagerOf(string $class): ?BaseManager {
        foreach ($this->managers as $manager) {
            if ($manager instanceof $class) {
                return $manager;
            }
        }

        return null;
    }

    public function hasManager(string $name): bool {
        return isset($this->managers[$name]);
    }
}
