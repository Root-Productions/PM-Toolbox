<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\network\mcpe\protocol\CameraShakePacket;
use pocketmine\network\mcpe\protocol\Packet;

final class ShakeCameraInstruction extends AbstractCameraInstruction {
    private int $type = CameraShakePacket::TYPE_POSITIONAL;

    private int $action = CameraShakePacket::ACTION_ADD;

    public function __construct(
        private float $intensity,
        private float $duration
    ) {
        self::requireNonNegative($intensity, "intensity");
        self::requireNonNegative($duration, "duration");
    }

    public function positional(): self {
        $this->type = CameraShakePacket::TYPE_POSITIONAL;
        return $this;
    }

    public function rotational(): self {
        $this->type = CameraShakePacket::TYPE_ROTATIONAL;
        return $this;
    }

    public function stop(): self {
        $this->action = CameraShakePacket::ACTION_STOP;
        return $this;
    }

    public function add(): self {
        $this->action = CameraShakePacket::ACTION_ADD;
        return $this;
    }

    public function toPacket(): Packet {
        return CameraShakePacket::create($this->intensity, $this->duration, $this->type, $this->action);
    }
}
