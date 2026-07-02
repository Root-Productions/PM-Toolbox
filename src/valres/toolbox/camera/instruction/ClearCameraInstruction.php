<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\Packet;

final class ClearCameraInstruction extends AbstractCameraInstruction {
    public function __construct(
        private bool $clear = true,
        private bool $removeTarget = true
    ) {
    }

    public function clear(bool $clear = true): self {
        $this->clear = $clear;
        return $this;
    }

    public function removeTarget(bool $removeTarget = true): self {
        $this->removeTarget = $removeTarget;
        return $this;
    }

    public function toPacket(): Packet {
        return CameraInstructionPacket::create(null, $this->clear, null, null, $this->removeTarget, null, null, null, null);
    }
}
