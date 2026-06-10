<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use pocketmine\form\Form as PMMPForm;
use pocketmine\player\Player;

abstract class BaseForm implements PMMPForm {
    private const TRACKING_OPEN = 0;
    private const TRACKING_HANDLING = 1;
    private const TRACKING_CLOSED = 2;

    protected array $data = [];
    private mixed $submitHandler;
    private mixed $closeHandler = null;
    private int $trackingGeneration = 0;
    /** @var array<string, array{generation: int, state: int}> */
    private array $trackingStates = [];

    public function __construct(?callable $submitHandler = null) {
        $this->submitHandler = $submitHandler;
    }

    public function getCallable(): ?callable {
        return is_callable($this->submitHandler) ? $this->submitHandler : null;
    }

    public function setCallable(?callable $callable): static {
        $this->submitHandler = $callable;
        return $this;
    }

    public function onSubmit(?callable $handler): static {
        return $this->setCallable($handler);
    }

    public function onClose(?callable $handler): static {
        $this->closeHandler = $handler;
        return $this;
    }

    public function setTitle(string $title): static {
        $this->data["title"] = $title;
        return $this;
    }

    public function getTitle(): string {
        return (string) ($this->data["title"] ?? "");
    }

    final public function handleResponse(Player $player, $data): void {
        $trackingGeneration = $this->beginTrackingHandle($player);
        if ($trackingGeneration === null) {
            return;
        }

        try {
            if ($data === null) {
                if (is_callable($this->closeHandler)) {
                    ($this->closeHandler)($player);
                }
                return;
            }

            $response = $this->processData($data);
            $this->handleProcessedResponse($player, $response, $data);
        } finally {
            $this->closeTrackingHandle($player, $trackingGeneration);
        }
    }

    protected function handleProcessedResponse(Player $player, mixed $response, mixed $rawData): void {
        $callable = $this->getCallable();
        if ($callable !== null) {
            $callable($player, $response, $rawData, $this);
        }
    }

    protected function processData(mixed $data): mixed {
        return $data;
    }

    public function jsonSerialize(): array {
        $this->trackingGeneration++;
        $this->cleanupTrackingStates();

        return $this->data;
    }

    public function isOpenFor(Player $player): bool {
        $state = $this->trackingStates[$this->getTrackingPlayerId($player)] ?? null;
        return $state === null
            || $state["generation"] !== $this->trackingGeneration
            || $state["state"] === self::TRACKING_OPEN;
    }

    public function isClosedFor(Player $player): bool {
        return !$this->isOpenFor($player);
    }

    private function beginTrackingHandle(Player $player): ?int {
        $playerId = $this->getTrackingPlayerId($player);
        $generation = $this->trackingGeneration;
        $state = $this->trackingStates[$playerId] ?? null;

        if ($state !== null
            && $state["generation"] === $generation
            && $state["state"] !== self::TRACKING_OPEN
        ) {
            return null;
        }

        $this->trackingStates[$playerId] = [
            "generation" => $generation,
            "state" => self::TRACKING_HANDLING
        ];

        return $generation;
    }

    private function closeTrackingHandle(Player $player, int $generation): void {
        $this->trackingStates[$this->getTrackingPlayerId($player)] = [
            "generation" => $generation,
            "state" => self::TRACKING_CLOSED
        ];
    }

    private function getTrackingPlayerId(Player $player): string {
        return $player->getUniqueId()->toString();
    }

    private function cleanupTrackingStates(): void {
        foreach ($this->trackingStates as $playerId => $state) {
            if ($state["generation"] < $this->trackingGeneration - 1) {
                unset($this->trackingStates[$playerId]);
            }
        }
    }
}
