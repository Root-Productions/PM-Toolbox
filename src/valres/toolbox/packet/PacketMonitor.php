<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use pocketmine\event\EventPriority;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\plugin\PluginBase;
use valres\toolbox\packet\exception\InvalidPacketHandlerException;

final class PacketMonitor extends PacketListener {
    public function __construct(PluginBase $plugin) {
        parent::__construct($plugin, EventPriority::MONITOR, false, true);
    }

    /**
     * @param class-string<Packet>[]|class-string<Packet>|null $packetClasses
     * @throws InvalidPacketHandlerException
     */
    public function monitorIncoming(callable $handler, array|string|null $packetClasses = null): self {
        $this->addIncoming($handler, $packetClasses);

        return $this;
    }

    /**
     * @param class-string<Packet>[]|class-string<Packet>|null $packetClasses
     * @throws InvalidPacketHandlerException
     */
    public function monitorOutgoing(callable $handler, array|string|null $packetClasses = null): self {
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
