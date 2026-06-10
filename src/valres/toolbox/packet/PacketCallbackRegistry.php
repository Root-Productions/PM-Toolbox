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
     * Registers callbacks executed for incoming packets matching any provided class.
     *
     * @param  class-string<Packet>[] $packetClasses
     * @param  callable               $handler
     *
     * @return void
     */
    public function registerIncoming(array $packetClasses, callable $handler): void {
        foreach ($packetClasses as $packetClass) {
            $this->incomingHandlers[$packetClass][] = $handler;
        }
    }

    /**
     * Registers callbacks executed for outgoing packets matching any provided class.
     *
     * @param  class-string<Packet>[] $packetClasses
     * @param  callable               $handler
     *
     * @return void
     */
    public function registerOutgoing(array $packetClasses, callable $handler): void {
        foreach ($packetClasses as $packetClass) {
            $this->outgoingHandlers[$packetClass][] = $handler;
        }
    }

    /**
     * Dispatches an incoming packet and returns false when a cancellable handler rejects it.
     *
     * @param  Packet         $packet
     * @param  NetworkSession $session
     * @param  bool           $cancellable
     *
     * @return bool
     */
    public function dispatchIncoming(Packet $packet, NetworkSession $session, bool $cancellable): bool {
        return $this->dispatch($this->incomingHandlers, $packet, $session, $cancellable);
    }

    /**
     * Dispatches an outgoing packet and returns false when a cancellable handler rejects it.
     *
     * @param  Packet         $packet
     * @param  NetworkSession $session
     * @param  bool           $cancellable
     *
     * @return bool
     */
    public function dispatchOutgoing(Packet $packet, NetworkSession $session, bool $cancellable): bool {
        return $this->dispatch($this->outgoingHandlers, $packet, $session, $cancellable);
    }

    /**
     * Dispatches a packet through matching handlers.
     *
     * @param  array<class-string<Packet>, callable[]> $handlers
     * @param  Packet                                  $packet
     * @param  NetworkSession                          $session
     * @param  bool                                    $cancellable
     *
     * @return bool
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
