<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use pocketmine\event\EventPriority;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\plugin\PluginBase;
use valres\toolbox\packet\exception\InvalidPacketHandlerException;

final class PacketInterceptor extends PacketListener {
    public function __construct(PluginBase $plugin, int $priority = EventPriority::NORMAL, bool $handleCancelled = false) {
        parent::__construct($plugin, $priority, true, $handleCancelled);
    }

    /**
     * @param class-string<Packet>[]|class-string<Packet>|null $packetClasses
     * @throws InvalidPacketHandlerException
     */
    public function interceptIncoming(callable $handler, array|string|null $packetClasses = null): self {
        $this->addIncoming($handler, $packetClasses);

        return $this;
    }

    /**
     * @param class-string<Packet>[]|class-string<Packet>|null $packetClasses
     * @throws InvalidPacketHandlerException
     */
    public function interceptOutgoing(callable $handler, array|string|null $packetClasses = null): self {
        $this->addOutgoing($handler, $packetClasses);

        return $this;
    }

    /** @throws InvalidPacketHandlerException */
    public function registerIncoming(PacketHandlerInterface $handler): self {
        $this->addIncomingHandler($handler);

        return $this;
    }

    /** @throws InvalidPacketHandlerException */
    public function registerOutgoing(PacketHandlerInterface $handler): self {
        $this->addOutgoingHandler($handler);

        return $this;
    }

    /** @throws InvalidPacketHandlerException */
    public function registerAnnotated(object $listener): self {
        $this->addAnnotated($listener);

        return $this;
    }
}
