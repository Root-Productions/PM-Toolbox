<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\Packet;

final class PacketCallbackRegistry {
    /** @var array<class-string<Packet>, callable[]> */
    private array $incomingHandlers = [];

    /** @var array<class-string<Packet>, callable[]> */
    private array $outgoingHandlers = [];

    /**
     * @param class-string<Packet>[] $packetClasses
     */
    public function registerIncoming(array $packetClasses, callable $handler): void {
        foreach ($packetClasses as $packetClass) {
            $this->incomingHandlers[$packetClass][] = $handler;
        }
    }

    /**
     * @param class-string<Packet>[] $packetClasses
     */
    public function registerOutgoing(array $packetClasses, callable $handler): void {
        foreach ($packetClasses as $packetClass) {
            $this->outgoingHandlers[$packetClass][] = $handler;
        }
    }

    public function dispatchIncoming(Packet $packet, NetworkSession $session, bool $cancellable): bool {
        return $this->dispatch($this->incomingHandlers, $packet, $session, $cancellable);
    }

    public function dispatchOutgoing(Packet $packet, NetworkSession $session, bool $cancellable): bool {
        return $this->dispatch($this->outgoingHandlers, $packet, $session, $cancellable);
    }

    /**
     * @param array<class-string<Packet>, callable[]> $handlers
     */
    private function dispatch(array $handlers, Packet $packet, NetworkSession $session, bool $cancellable): bool {
        foreach ($handlers as $packetClass => $packetHandlers) {
            if (!$packet instanceof $packetClass) {
                continue;
            }

            foreach ($packetHandlers as $handler) {
                $result = $handler($packet, $session);

                if ($cancellable && $result === false) {
                    return false;
                }
            }
        }

        return true;
    }
}
