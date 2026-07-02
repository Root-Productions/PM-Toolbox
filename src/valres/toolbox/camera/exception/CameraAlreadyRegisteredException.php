<?php

declare(strict_types=1);

namespace valres\toolbox\camera\exception;

use pocketmine\plugin\PluginBase;

final class CameraAlreadyRegisteredException extends CameraException {
    public static function forPlugin(PluginBase $plugin): self {
        return new self("Camera toolbox is already registered by {$plugin->getName()}.");
    }
}
