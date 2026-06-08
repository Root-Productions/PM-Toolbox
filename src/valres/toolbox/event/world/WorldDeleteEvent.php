<?php

declare(strict_types=1);

namespace valres\toolbox\event\world;

use pocketmine\event\Event;

class WorldDeleteEvent extends Event {
    public function __construct(
        private readonly string $worldName,
        private readonly string $worldPath,
        private readonly int $removedFiles
    ) {
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getWorldPath(): string {
        return $this->worldPath;
    }

    public function getRemovedFiles(): int {
        return $this->removedFiles;
    }
}
