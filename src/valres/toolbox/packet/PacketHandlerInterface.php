<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\Packet;

interface PacketHandlerInterface {
    /** @return array<class-string<Packet>> */
    public function getPacketIds(): array;

    /**
     * Return false from an interceptor to cancel the packet event.
     * Monitor return values are ignored.
     */
    public function handle(Packet $packet, NetworkSession $session): bool;
}
