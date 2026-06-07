<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use pocketmine\utils\TextFormat;
use valres\toolbox\ToolboxLoader;

final class ManagerLogger {
    public function info(string $message): void {
        ToolboxLoader::getLoader()->getLogger()->info(TextFormat::AQUA . $message);
    }

    public function error(string $message): void {
        ToolboxLoader::getLoader()->getLogger()->error(TextFormat::RED . $message);
    }

    public function sanitizeError(string $message): string {
        return $message;
    }
}
