<?php

declare(strict_types=1);

namespace valres\toolbox\behavior;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use valres\toolbox\behavior\block\BlockPaletteEntryMapper;
use valres\toolbox\packet\attribute\OutgoingPacket;

final class BehaviorInterceptor {
    private ?Experiments $experiments;

    public function __construct() {
        $this->experiments = new Experiments([
            "data_driven_items" => true
        ], true);
    }

    #[OutgoingPacket(StartGamePacket::class)]
    public function onStartGame(StartGamePacket $pk, NetworkSession $session): bool {
        $pk->levelSettings->experiments = $this->experiments;
        $entries = BlockPaletteEntryMapper::getInstance()->getEntries();
        if ($entries !== []) {
            $pk->blockNetworkIdsAreHashes = true;
            $pk->blockPalette = $entries;
        }
        return true;
    }

    #[OutgoingPacket(ResourcePackStackPacket::class)]
    public function onResourcePackStack(ResourcePackStackPacket $pk, NetworkSession $session): bool {
        $pk->experiments = $this->experiments;
        return true;
    }
}
