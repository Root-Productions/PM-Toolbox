<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use InvalidArgumentException;
use LogicException;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUIShowScreenPacket;
use pocketmine\network\mcpe\protocol\ClientboundDataStorePacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use valres\toolbox\form\ddui\operation\DataStoreChangeOperation;

final class DduiManager {
    /**
     * @var array<string, array<int, array{player: Player, screen: DduiScreen, formId: int, property: string, updateCount: int}>>
     */
    private static array $activeScreens = [];

    private static ?PluginBase $registrant = null;
    private static int $nextId = 1;

    private function __construct() {}

    public static function register(PluginBase $plugin): void {
        if (self::isRegistered()) {
            throw new InvalidArgumentException(self::class . " is already registered.");
        }

        self::$registrant = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents(new DduiPacketListener(), $plugin);
    }

    public static function isRegistered(): bool {
        return self::$registrant instanceof PluginBase;
    }

    public static function getRegistrant(): PluginBase {
        return self::$registrant ?? throw new LogicException("Cannot access DduiManager registrant before registration.");
    }

    public static function customForm(string $title): DduiCustomForm {
        return new DduiCustomForm($title);
    }

    public static function messageBox(string $title, string $body = ""): DduiMessageBox {
        return new DduiMessageBox($title, $body);
    }

    public static function send(Player $player, DduiScreen $screen): void {
        if (!self::isRegistered()) {
            throw new LogicException("Cannot send a data-driven UI before " . self::class . " registration.");
        }

        $formId = self::$nextId++;
        $dataInstanceId = self::$nextId++;
        $property = self::deriveProperty($screen->getScreenId(), $dataInstanceId);

        self::$activeScreens[self::playerKey($player)][$dataInstanceId] = [
            "player" => $player,
            "screen" => $screen,
            "formId" => $formId,
            "property" => $property,
            "updateCount" => 1,
        ];

        $player->getNetworkSession()->sendDataPacket(ClientboundDataStorePacket::create([
            new DataStoreChangeOperation("minecraft", $property, 1, $screen->serializeData()),
        ]));

        $player->getNetworkSession()->sendDataPacket(
            ClientboundDataDrivenUIShowScreenPacket::create($screen->getScreenId(), $formId, $dataInstanceId)
        );
    }

    public static function close(Player $player, int $formId, int $reason): bool {
        foreach (self::$activeScreens[self::playerKey($player)] ?? [] as $instanceId => $entry) {
            if ($entry["formId"] !== $formId) {
                continue;
            }

            unset(self::$activeScreens[self::playerKey($player)][$instanceId]);
            $entry["screen"]->handleClose($player, $reason);
            DduiPacketListener::sendClosePacket($player, $entry);

            return true;
        }

        return false;
    }

    public static function update(Player $player, string $property, string $path, bool|string|float $value): bool {
        $instanceId = self::extractInstanceId($property);
        if ($instanceId === null) {
            return false;
        }

        $entry = self::$activeScreens[self::playerKey($player)][$instanceId] ?? null;
        if ($entry === null) {
            return false;
        }

        self::$activeScreens[self::playerKey($player)][$instanceId]["updateCount"]++;
        $entry["updateCount"]++;
        $shouldClose = $entry["screen"]->handleUpdate($player, $path, $value);
        if ($shouldClose) {
            unset(self::$activeScreens[self::playerKey($player)][$instanceId]);
            $entry["screen"]->handleClose($player, DduiCloseReason::PROGRAMMATIC);
            DduiPacketListener::sendClosePacket($player, $entry);
        }

        return true;
    }

    public static function refreshScreen(DduiScreen $screen): void {
        foreach (self::$activeScreens as $playerKey => $screens) {
            foreach ($screens as $instanceId => $entry) {
                if ($entry["screen"] !== $screen) {
                    continue;
                }

                $player = $entry["player"];
                if (!$player->isConnected()) {
                    unset(self::$activeScreens[$playerKey][$instanceId]);
                    continue;
                }

                self::$activeScreens[$playerKey][$instanceId]["updateCount"]++;
                $updateCount = self::$activeScreens[$playerKey][$instanceId]["updateCount"];
                $player->getNetworkSession()->sendDataPacket(ClientboundDataStorePacket::create([
                    new DataStoreChangeOperation("minecraft", $entry["property"], $updateCount, $screen->serializeData()),
                ]));
            }
        }
    }

    public static function forget(Player $player): void {
        unset(self::$activeScreens[self::playerKey($player)]);
    }

    /**
     * @internal
     *
     * @return array<int, array{player: Player, screen: DduiScreen, formId: int, property: string, updateCount: int}>
     */
    public static function getActiveScreens(Player $player): array {
        return self::$activeScreens[self::playerKey($player)] ?? [];
    }

    private static function deriveProperty(string $screenId, int $dataInstanceId): string {
        $base = str_starts_with($screenId, "minecraft:") ? substr($screenId, strlen("minecraft:")) : $screenId;

        return str_replace(":", "_", $base) . "_data_" . $dataInstanceId;
    }

    private static function extractInstanceId(string $property): ?int {
        $separator = strrpos($property, "_data_");

        return $separator === false ? null : (int) substr($property, $separator + 6);
    }

    private static function playerKey(Player $player): string {
        return $player->getUniqueId()->toString();
    }
}
