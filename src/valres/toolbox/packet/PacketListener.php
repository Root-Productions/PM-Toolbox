<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\plugin\PluginBase;
use ReflectionClass;
use ReflectionMethod;
use valres\toolbox\packet\attribute\IncomingPacket;
use valres\toolbox\packet\attribute\OutgoingPacket;
use valres\toolbox\packet\attribute\PacketHandler;
use valres\toolbox\packet\exception\InvalidPacketHandlerException;

abstract class PacketListener {
    private PacketCallbackRegistry $registry;

    private PacketCallbackResolver $resolver;

    public function __construct(
        private readonly PluginBase $plugin,
        private readonly int $priority,
        private readonly bool $cancellable,
        private readonly bool $handleCancelled
    ) {
        $this->registry = new PacketCallbackRegistry();
        $this->resolver = new PacketCallbackResolver();
        $this->registerEvents();
    }

    /**
     * @param class-string[]|class-string|null $packetClasses
     * @throws InvalidPacketHandlerException
     */
    protected function addIncoming(callable $handler, array|string|null $packetClasses = null): void {
        $this->registry->registerIncoming($this->resolver->resolvePacketClasses($handler, $packetClasses), $handler);
    }

    /**
     * @param class-string[]|class-string|null $packetClasses
     * @throws InvalidPacketHandlerException
     */
    protected function addOutgoing(callable $handler, array|string|null $packetClasses = null): void {
        $this->registry->registerOutgoing($this->resolver->resolvePacketClasses($handler, $packetClasses), $handler);
    }

    /**
     * @throws InvalidPacketHandlerException
     */
    protected function addIncomingHandler(PacketHandlerInterface $handler): void {
        $this->addIncoming([$handler, "handle"], $handler->getPacketIds());
    }

    /**
     * @throws InvalidPacketHandlerException
     */
    protected function addOutgoingHandler(PacketHandlerInterface $handler): void {
        $this->addOutgoing([$handler, "handle"], $handler->getPacketIds());
    }

    /**
     * @throws InvalidPacketHandlerException
     */
    protected function addAnnotated(object $listener): void {
        $reflection = new ReflectionClass($listener);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($method->getAttributes(IncomingPacket::class) as $attribute) {
                $packets = $attribute->newInstance()->packets;
                $this->addIncoming([$listener, $method->getName()], $packets);
            }

            foreach ($method->getAttributes(OutgoingPacket::class) as $attribute) {
                $packets = $attribute->newInstance()->packets;
                $this->addOutgoing([$listener, $method->getName()], $packets);
            }

            foreach ($method->getAttributes(PacketHandler::class) as $attribute) {
                $handler = $attribute->newInstance();

                match ($handler->direction) {
                    PacketDirection::INCOMING => $this->addIncoming([$listener, $method->getName()], $handler->packets),
                    PacketDirection::OUTGOING => $this->addOutgoing([$listener, $method->getName()], $handler->packets),
                };
            }
        }
    }

    private function registerEvents(): void {
        $pluginManager = $this->plugin->getServer()->getPluginManager();

        $pluginManager->registerEvent(
            DataPacketReceiveEvent::class,
            $this->onDataPacketReceive(...),
            $this->priority,
            $this->plugin,
            $this->handleCancelled
        );

        $pluginManager->registerEvent(
            DataPacketSendEvent::class,
            $this->onDataPacketSend(...),
            $this->priority,
            $this->plugin,
            $this->handleCancelled
        );
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        if (!$this->registry->dispatchIncoming($event->getPacket(), $event->getOrigin(), $this->cancellable)) {
            $event->cancel();
        }
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void {
        foreach ($event->getPackets() as $packet) {
            foreach ($event->getTargets() as $target) {
                if (!$this->registry->dispatchOutgoing($packet, $target, $this->cancellable)) {
                    $event->cancel();
                    return;
                }
            }
        }
    }

    public function getPlugin(): PluginBase {
        return $this->plugin;
    }

    public function getPriority(): int {
        return $this->priority;
    }

    public function isCancellable(): bool {
        return $this->cancellable;
    }
}
