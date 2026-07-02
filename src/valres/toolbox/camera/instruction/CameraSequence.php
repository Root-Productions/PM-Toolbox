<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\player\Player;

final class CameraSequence {
    /** @var CameraInstruction[] */
    private array $instructions = [];

    public function add(CameraInstruction $instruction): self {
        $this->instructions[] = $instruction;
        return $this;
    }

    /** @return CameraInstruction[] */
    public function getInstructions(): array {
        return $this->instructions;
    }

    public function send(Player $player): void {
        foreach ($this->instructions as $instruction) {
            $instruction->send($player);
        }
    }
}
