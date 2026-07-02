<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEase;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionEaseType;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstructionRotation;
use valres\toolbox\camera\CameraPresets;

final class SetCameraInstruction extends AbstractCameraInstruction {
    private ?CameraSetInstructionEase $ease = null;

    private ?Vector3 $cameraPosition = null;

    private ?CameraSetInstructionRotation $rotation = null;

    private ?Vector3 $facingPosition = null;

    private ?Vector2 $viewOffset = null;

    private ?Vector3 $entityOffset = null;

    private ?bool $default = null;

    private bool $ignoreStartingValuesComponent = true;

    public function __construct(private CameraPreset $preset) {
    }

    public function preset(CameraPreset $preset): self {
        $this->preset = $preset;
        return $this;
    }

    public function ease(float $duration, int $type = CameraSetInstructionEaseType::LINEAR): self {
        $this->ease = new CameraSetInstructionEase($type, self::requireNonNegative($duration, "duration"));
        return $this;
    }

    public function position(Vector3 $position): self {
        $this->cameraPosition = $position;
        return $this;
    }

    public function rotation(float $pitch, float $yaw): self {
        $this->rotation = new CameraSetInstructionRotation($pitch, $yaw);
        return $this;
    }

    public function facing(Vector3 $position): self {
        $this->facingPosition = $position;
        return $this;
    }

    public function viewOffset(Vector2 $offset): self {
        $this->viewOffset = $offset;
        return $this;
    }

    public function entityOffset(Vector3 $offset): self {
        $this->entityOffset = $offset;
        return $this;
    }

    public function useDefault(bool $default = true): self {
        $this->default = $default;
        return $this;
    }

    public function ignoreStartingValues(bool $ignore = true): self {
        $this->ignoreStartingValuesComponent = $ignore;
        return $this;
    }

    public function toPacket(): Packet {
        return CameraInstructionPacket::create(
            new CameraSetInstruction(
                CameraPresets::indexOf($this->preset),
                $this->ease,
                $this->cameraPosition,
                $this->rotation,
                $this->facingPosition,
                $this->viewOffset,
                $this->entityOffset,
                $this->default,
                $this->ignoreStartingValuesComponent
            ),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }
}
