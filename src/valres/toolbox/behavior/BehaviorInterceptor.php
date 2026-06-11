<?php

declare(strict_types=1);

namespace valres\toolbox\behavior;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use valres\toolbox\behavior\block\BlockPaletteEntryMapper;
use valres\toolbox\packet\attribute\IncomingPacket;

final class BehaviorInterceptor {
    private ?Experiments $experiments;

    public function __construct() {
        $this->experiments = new Experiments([
            "data_driven_items" => true
        ], true);
    }

    #[IncomingPacket(StartGamePacket::class)]
    public function onStartGame(StartGamePacket $pk, NetworkSession $session): bool {
        $pk->levelSettings->experiments = $this->experiments;
        $pk->blockNetworkIdsAreHashes = true;
        $pk->blockPalette = BlockPaletteEntryMapper::getInstance()->getEntries();
        return true;
    }

    #[IncomingPacket(ResourcePackStackPacket::class)]
    public function onResourcePackStack(ResourcePackStackPacket $pk, NetworkSession $session): bool {
        $pk->experiments = $this->experiments;
        return true;
    }
}
