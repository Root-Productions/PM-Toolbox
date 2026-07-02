<?php

declare(strict_types=1);

namespace valres\toolbox\camera\exception;

use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;

final class CameraPresetException extends CameraException {
    public static function duplicate(string $name): self {
        return new self("Camera preset '{$name}' is already registered.");
    }

    public static function unknownName(string $name): self {
        return new self("Camera preset '{$name}' is not registered.");
    }

    public static function unknown(CameraPreset $preset): self {
        return new self("Camera preset '{$preset->getName()}' is not registered.");
    }
}
