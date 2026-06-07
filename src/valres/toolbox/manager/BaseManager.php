<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use GlobalLogger;
use Logger;
use pocketmine\plugin\PluginBase;
use valres\toolbox\manager\exception\ManagerException;
use valres\toolbox\ToolboxLoader;

abstract class BaseManager {
    use ManagerSingletonTrait;

    protected bool $isEnabled = false;

    protected readonly Logger $logger;

    protected PluginBase $loader;

    private ManagerMetadataReader $metadataReader;

    public function __construct() {
        $this->loader = ToolboxLoader::getLoader();
        $this->logger = GlobalLogger::get();
        $this->metadataReader = new ManagerMetadataReader();
        $this->registerManagerInstance();
    }

    /**
     * Return the name of the manager.
     *
     * @return string
     */
    public function getName(): string {
        return $this->metadataReader->getName($this);
    }

    /**
     * Called during PluginBase::onLoad().
     * Keep it LIGHT: config parsing, class maps, reading files into memory, etc.
     * Avoid using Server-heavy stuff if possible.
     */
    public function onLoad(): void {
        // optional, override if needed
    }

    /**
     * Initializes the manager.
     *
     * @throws ManagerException if initialization fails.
     */
    abstract public function init(): void;

    /**
     * Saves persistent data before shutdown.
     *
     * @throws ManagerException if saving fails.
     */
    abstract public function save(): void;

    public function getInitializationSummary(): ?array {
        return null;
    }

    public function getLogger(): Logger {
        return $this->logger;
    }

    public function getVersion(): string {
        return $this->metadataReader->getVersion($this);
    }

    /**
     * Returns the manager's priority.
     *
     * @return int
     */
    public function getPriority(): int {
        return $this->metadataReader->getPriority($this);
    }

    /**
     * Returns a list of required manager names.
     *
     * @return string[]
     */
    public function getRequiredManagers(): array {
        return $this->metadataReader->getRequiredManagers($this);
    }

    public function isEnabled(): bool {
        return $this->isEnabled;
    }

    public function setEnabled(bool $enabled = true): void {
        $this->isEnabled = $enabled;
    }

    protected function markEnabled(): void {
        $this->isEnabled = true;
    }
}
