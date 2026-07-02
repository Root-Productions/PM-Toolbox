<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\network\mcpe\protocol\CameraInstructionPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime;

final class FadeCameraInstruction extends AbstractCameraInstruction {
    private ?CameraFadeInstructionTime $time = null;

    private ?CameraFadeInstructionColor $color = null;

    public function time(float $fadeInTime, float $stayTime, float $fadeOutTime): self {
        $this->time = new CameraFadeInstructionTime(
            self::requireNonNegative($fadeInTime, "fadeInTime"),
            self::requireNonNegative($stayTime, "stayTime"),
            self::requireNonNegative($fadeOutTime, "fadeOutTime")
        );

        return $this;
    }

    public function color(float $red, float $green, float $blue): self {
        $this->color = new CameraFadeInstructionColor(
            self::requireRange($red, 0, 1, "red"),
            self::requireRange($green, 0, 1, "green"),
            self::requireRange($blue, 0, 1, "blue")
        );

        return $this;
    }

    public function rgb(int $red, int $green, int $blue): self {
        return $this->color(
            self::requireRange($red, 0, 255, "red") / 255,
            self::requireRange($green, 0, 255, "green") / 255,
            self::requireRange($blue, 0, 255, "blue") / 255
        );
    }

    public function toPacket(): Packet {
        return CameraInstructionPacket::create(null, null, new CameraFadeInstruction($this->time, $this->color), null, null, null, null, null, null);
    }
}
