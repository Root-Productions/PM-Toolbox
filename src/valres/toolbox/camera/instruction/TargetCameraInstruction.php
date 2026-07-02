<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\types\camera\CameraTargetInstruction;

final class TargetCameraInstruction extends AbstractCameraInstruction {
    private ?Vector3 $targetCenterOffset = null;

    public function __construct(private int $actorUniqueId) {
    }

    public function offset(Vector3 $offset): self {
        $this->targetCenterOffset = $offset;
        return $this;
    }

    public function toPacket(): Packet {
        return CameraInstructionPacket::create(null, null, null, new CameraTargetInstruction($this->targetCenterOffset, $this->actorUniqueId), null, null, null, null, null);
    }
}
