<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUICloseScreenPacket;
use pocketmine\network\mcpe\protocol\ClientboundDataStorePacket;
use pocketmine\network\mcpe\protocol\ServerboundDataDrivenScreenClosedPacket;
use pocketmine\network\mcpe\protocol\ServerboundDataStorePacket;
use pocketmine\network\mcpe\protocol\types\ddui\update\BoolDataStoreUpdateValue;
use pocketmine\network\mcpe\protocol\types\ddui\update\DoubleDataStoreUpdateValue;
use pocketmine\network\mcpe\protocol\types\ddui\update\StringDataStoreUpdateValue;
use pocketmine\player\Player;
use valres\toolbox\form\ddui\operation\DataStoreChangeOperation;

final class DduiPacketListener implements Listener {
    /**
     * @handleCancelled
     */
    public function onDecode(DataPacketDecodeEvent $event): void {
        $packetId = $event->getPacketId();
        if ($packetId === ServerboundDataDrivenScreenClosedPacket::NETWORK_ID || $packetId === ServerboundDataStorePacket::NETWORK_ID) {
            $event->uncancel();
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        DduiManager::forget($event->getPlayer());
    }

    public function onReceive(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if ($player === null) {
            return;
        }

        if ($packet instanceof ServerboundDataDrivenScreenClosedPacket) {
            DduiManager::close($player, $packet->getFormId(), $this->mapCloseReason($packet->getCloseReason()));
            return;
        }

        if (!$packet instanceof ServerboundDataStorePacket) {
            return;
        }

        $update = $packet->getUpdate();
        $value = match (true) {
            $update->getData() instanceof BoolDataStoreUpdateValue => $update->getData()->getValue(),
            $update->getData() instanceof StringDataStoreUpdateValue => $update->getData()->getValue(),
            $update->getData() instanceof DoubleDataStoreUpdateValue => $update->getData()->getValue(),
            default => null,
        };

        if ($value !== null) {
            DduiManager::update($player, $update->getProperty(), $update->getPath(), $value);
        }
    }

    /**
     * @param array{player: Player, screen: DduiScreen, formId: int, property: string, updateCount: int} $entry
     */
    public static function sendClosePacket(Player $player, array $entry): void {
        $player->getNetworkSession()->sendDataPacket(
            ClientboundDataDrivenUICloseScreenPacket::create($entry["formId"])
        );

        $player->getNetworkSession()->sendDataPacket(ClientboundDataStorePacket::create([
            new DataStoreChangeOperation(
                "minecraft",
                $entry["property"],
                $entry["updateCount"] + 1,
                null
            ),
        ]));
    }

    private function mapCloseReason(string $reason): int {
        $reason = strtolower($reason);

        return match (true) {
            str_contains($reason, "programmaticcloseall") => DduiCloseReason::PROGRAMMATIC_ALL,
            str_contains($reason, "programmaticclose") => DduiCloseReason::PROGRAMMATIC,
            str_contains($reason, "usy") => DduiCloseReason::BUSY,
            str_contains($reason, "nvalid") => DduiCloseReason::INVALID,
            default => DduiCloseReason::CLIENT_CLOSED,
        };
    }
}
