<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\types\camera\CameraFovInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;

final class FovCameraInstruction extends AbstractCameraInstruction {
    private float $easeTime = 0.0;

    private int|string $easeType = CameraSetInstructionEaseType::LINEAR;

    private bool $clear = false;

    public function __construct(private float $fieldOfView) {
        self::requireRange($fieldOfView, 1, 179, "fieldOfView");
    }

    public function ease(float $duration, int|string $type = CameraSetInstructionEaseType::LINEAR): self {
        $this->easeTime = self::requireNonNegative($duration, "duration");
        $this->easeType = $type;
        return $this;
    }

    public function clear(bool $clear = true): self {
        $this->clear = $clear;
        return $this;
    }

    public function toPacket(): Packet {
        return CameraInstructionPacket::create(null, null, null, null, null, new CameraFovInstruction($this->fieldOfView, $this->easeTime, $this->easeType, $this->clear), null, null, null);
    }
}
