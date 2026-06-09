<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUICloseScreenPacket;
use pocketmine\network\mcpe\protocol\ServerboundDataDrivenScreenClosedPacket;
use pocketmine\network\mcpe\protocol\ServerboundDataStorePacket;
use pocketmine\network\mcpe\protocol\types\BoolDataStoreValue;
use pocketmine\network\mcpe\protocol\types\DataStoreUpdate;
use pocketmine\network\mcpe\protocol\types\DataStoreValue;
use pocketmine\network\mcpe\protocol\types\DoubleDataStoreValue;
use pocketmine\network\mcpe\protocol\types\StringDataStoreValue;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use valres\toolbox\form\ddui\packet\DduiDataStorePacket;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiDataStoreChange;
use valres\toolbox\form\ddui\packet\type\DduiNoneValue;
use valres\toolbox\form\ddui\packet\type\DduiStringValue;
use valres\toolbox\ToolboxLoader;

final class DduiManager {
    private static ?self $instance = null;
    private int $formIdCounter = 1;

    /** @var array<string, int> */
    private array $updateCounts = [];
    /** @var array<string, int> */
    private array $activeFormIds = [];
    /** @var array<string, array<int, DduiForm>> */
    private array $forms = [];
    /** @var array<string, array<int, array<string, DduiObservable>>> */
    private array $bindings = [];
    /** @var array<int, array<array{player: string, form: int, path: string}>> */
    private array $bindingsByObservable = [];
    /** @var array<string, array<int, array<string, callable>>> */
    private array $clickHandlers = [];
    /** @var array<string, array<string, int>> */
    private array $pathUpdateCounts = [];
    /** @var array<string, array<int, array<int, array{visible: bool, disabled: bool}>>> */
    private array $elementStates = [];

    private function __construct(Plugin $plugin) {
        $pluginManager = Server::getInstance()->getPluginManager();

        $pluginManager->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event): void {
            $this->clearPlayer($event->getPlayer()->getUniqueId()->getBytes());
        }, EventPriority::LOWEST, $plugin);

        $pluginManager->registerEvent(DataPacketDecodeEvent::class, function(DataPacketDecodeEvent $event): void {
            if ($event->getPacketId() === ServerboundDataStorePacket::NETWORK_ID || $event->getPacketId() === ServerboundDataDrivenScreenClosedPacket::NETWORK_ID) {
                $event->uncancel();
            }
        }, EventPriority::LOWEST, $plugin, true);

        $pluginManager->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event): void {
            $packet = $event->getPacket();
            $player = $event->getOrigin()->getPlayer();
            if (!$player instanceof Player) {
                return;
            }

            if ($packet instanceof ServerboundDataStorePacket) {
                $this->handleDataStore($event, $player, $packet);
            } elseif ($packet instanceof ServerboundDataDrivenScreenClosedPacket) {
                $this->handleScreenClosed($event, $player, $packet);
            }
        }, EventPriority::LOW, $plugin);
    }

    public static function init(Plugin $plugin): self {
        return self::$instance ??= new self($plugin);
    }

    public static function getInstance(): ?self {
        return self::$instance;
    }

    public static function getOrCreate(): self {
        return self::$instance ??= new self(ToolboxLoader::getLoader());
    }

    public function nextFormId(): int {
        return $this->formIdCounter++;
    }

    public function nextUpdateCountFor(string $playerUuid): int {
        return $this->updateCounts[$playerUuid] = ($this->updateCounts[$playerUuid] ?? 0) + 1;
    }

    public function hasActiveForm(string $playerUuid): bool {
        return isset($this->activeFormIds[$playerUuid]);
    }

    public function registerForm(string $playerUuid, int $formId, DduiForm $form): void {
        $this->activeFormIds[$playerUuid] = $formId;
        $this->forms[$playerUuid][$formId] = $form;
    }

    public function registerObservableBinding(string $playerUuid, int $formId, string $path, DduiObservable $observable): void {
        $this->bindings[$playerUuid][$formId][$path] = $observable;
        $this->bindingsByObservable[spl_object_id($observable)][] = ["player" => $playerUuid, "form" => $formId, "path" => $path];
    }

    public function registerClickHandler(string $playerUuid, int $formId, string $path, callable $handler): void {
        $this->clickHandlers[$playerUuid][$formId][$path] = $handler;
    }

    public function registerElementState(string $playerUuid, int $formId, int $index, bool $visible, bool $disabled): void {
        $this->elementStates[$playerUuid][$formId][$index] = ["visible" => $visible, "disabled" => $disabled];
    }

    public function notifyObservableChanged(DduiObservable $observable): void {
        foreach ($this->bindingsByObservable[spl_object_id($observable)] ?? [] as $binding) {
            $this->sendObservableUpdate($binding["player"], $binding["form"], $binding["path"], $observable->get());
        }
    }

    private function handleDataStore(DataPacketReceiveEvent $event, Player $player, ServerboundDataStorePacket $packet): void {
        $update = $packet->getUpdate();
        if ($update->getName() !== "minecraft" || $update->getProperty() !== "custom_form_data") {
            return;
        }

        $playerUuid = $player->getUniqueId()->getBytes();
        $formId = $this->activeFormIds[$playerUuid] ?? null;
        if ($formId === null) {
            return;
        }

        $path = $update->getPath();
        if ($this->isInteractionBlocked($playerUuid, $formId, $path)) {
            $event->cancel();
            return;
        }

        if ($path === "closeButton.onClick") {
            $event->cancel();
            $player->getNetworkSession()->sendDataPacket(ClientboundDataDrivenUICloseScreenPacket::create($formId));
            return;
        }

        if (isset($this->clickHandlers[$playerUuid][$formId][$path])) {
            $event->cancel();
            ($this->clickHandlers[$playerUuid][$formId][$path])($this->readDataStoreValue($update->getData()));
            return;
        }

        $observable = $this->bindings[$playerUuid][$formId][$path] ?? null;
        if (!$observable instanceof DduiObservable) {
            return;
        }

        $event->cancel();
        if (!$observable->isClientWritable()) {
            return;
        }

        $value = $this->readDataStoreValue($update->getData());
        if (!is_bool($value) && !is_float($value) && !is_int($value) && !is_string($value)) {
            return;
        }

        $this->trackElementState($playerUuid, $formId, $path, $value);
        $observable->applyClientValue($value);
        $this->notifyObservableChangedFromClient($observable, $playerUuid, $formId, $path);
    }

    private function handleScreenClosed(DataPacketReceiveEvent $event, Player $player, ServerboundDataDrivenScreenClosedPacket $packet): void {
        $event->cancel();
        $playerUuid = $player->getUniqueId()->getBytes();
        $formId = $packet->getFormId();
        $updateCount = $this->nextUpdateCountFor($playerUuid);

        $player->getNetworkSession()->sendDataPacket(DduiDataStorePacket::create([
            new DduiDataStoreChange("minecraft", "ddui_form_active", $updateCount, new DduiBoolValue(false)),
            new DduiDataStoreChange("minecraft", "custom_form_data", $updateCount, new DduiNoneValue()),
        ]));

        if (isset($this->forms[$playerUuid][$formId])) {
            $this->forms[$playerUuid][$formId]->markClosed();
        }
        $this->clearForm($playerUuid, $formId);
    }

    private function sendObservableUpdate(string $playerUuid, int $formId, string $path, bool|float|int|string $value): void {
        if (($this->activeFormIds[$playerUuid] ?? null) !== $formId) {
            return;
        }

        $player = Server::getInstance()->getPlayerByRawUUID($playerUuid);
        if (!$player instanceof Player) {
            return;
        }

        $pathUpdate = ($this->pathUpdateCounts[$playerUuid][$path] ?? 0) + 1;
        $this->pathUpdateCounts[$playerUuid][$path] = $pathUpdate;
        $this->trackElementState($playerUuid, $formId, $path, $value);

        $player->getNetworkSession()->sendDataPacket(DduiDataStorePacket::create([
            new DataStoreUpdate(
                "minecraft",
                "custom_form_data",
                $path,
                $this->toNativeDataStoreValue($value),
                $this->updateCounts[$playerUuid] ?? 1,
                $pathUpdate
            )
        ]));
    }

    private function notifyObservableChangedFromClient(DduiObservable $observable, string $sourcePlayerUuid, int $sourceFormId, string $sourcePath): void {
        foreach ($this->bindingsByObservable[spl_object_id($observable)] ?? [] as $binding) {
            if ($binding["player"] === $sourcePlayerUuid && $binding["form"] === $sourceFormId && $binding["path"] === $sourcePath) {
                continue;
            }
            $this->sendObservableUpdate($binding["player"], $binding["form"], $binding["path"], $observable->get());
        }
    }

    private function readDataStoreValue(DataStoreValue $value): mixed {
        return method_exists($value, "getValue") ? $value->getValue() : null;
    }

    private function toNativeDataStoreValue(bool|float|int|string $value): DataStoreValue {
        if (is_bool($value)) {
            return new BoolDataStoreValue($value);
        }
        if (is_int($value) || is_float($value)) {
            return new DoubleDataStoreValue((float) $value);
        }
        return new StringDataStoreValue($value);
    }

    private function isInteractionBlocked(string $playerUuid, int $formId, string $path): bool {
        if (!preg_match('/^layout\[(\d+)]\.(.+)$/', $path, $matches)) {
            return false;
        }

        $property = $matches[2];
        if ($property === "visible" || $property === "disabled") {
            return false;
        }

        $state = $this->elementStates[$playerUuid][$formId][(int) $matches[1]] ?? null;
        return $state !== null && ($state["disabled"] || !$state["visible"]);
    }

    private function trackElementState(string $playerUuid, int $formId, string $path, mixed $value): void {
        if (!preg_match('/^layout\[(\d+)]\.(visible|disabled)$/', $path, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        $state = $this->elementStates[$playerUuid][$formId][$index] ?? ["visible" => true, "disabled" => false];
        $state[$matches[2]] = (bool) $value;
        $this->elementStates[$playerUuid][$formId][$index] = $state;
    }

    private function clearForm(string $playerUuid, int $formId): void {
        unset($this->forms[$playerUuid][$formId], $this->bindings[$playerUuid][$formId], $this->clickHandlers[$playerUuid][$formId], $this->elementStates[$playerUuid][$formId]);
        if (($this->activeFormIds[$playerUuid] ?? null) === $formId) {
            unset($this->activeFormIds[$playerUuid], $this->pathUpdateCounts[$playerUuid]);
        }

        foreach ($this->bindingsByObservable as $observableId => $bindings) {
            $this->bindingsByObservable[$observableId] = array_values(array_filter(
                $bindings,
                static fn(array $binding): bool => !($binding["player"] === $playerUuid && $binding["form"] === $formId)
            ));
            if ($this->bindingsByObservable[$observableId] === []) {
                unset($this->bindingsByObservable[$observableId]);
            }
        }
    }

    private function clearPlayer(string $playerUuid): void {
        unset($this->updateCounts[$playerUuid], $this->activeFormIds[$playerUuid], $this->forms[$playerUuid], $this->bindings[$playerUuid], $this->clickHandlers[$playerUuid], $this->pathUpdateCounts[$playerUuid], $this->elementStates[$playerUuid]);
    }
}
