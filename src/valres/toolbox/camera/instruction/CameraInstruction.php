<?php

declare(strict_types=1);

namespace valres\toolbox\camera\instruction;

use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\player\Player;

interface CameraInstruction {
    public function toPacket(): Packet;

    public function send(Player $player): void;
}
