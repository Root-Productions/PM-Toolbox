<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\Packet;

final class AttachCameraInstruction extends AbstractCameraInstruction {
    private function __construct(
        private ?int $actorUniqueId,
        private ?bool $detach
    ) {
    }

    public static function attach(int $actorUniqueId): self {
        return new self($actorUniqueId, null);
    }

    public static function detach(): self {
        return new self(null, true);
    }

    public function toPacket(): Packet {
        return CameraInstructionPacket::create(null, null, null, null, null, null, null, $this->actorUniqueId, $this->detach);
    }
}
