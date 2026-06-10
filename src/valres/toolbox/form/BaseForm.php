<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use pocketmine\form\Form as PMMPForm;
use pocketmine\player\Player;

abstract class BaseForm implements PMMPForm {
    private const STATE_OPEN = 0;
    private const STATE_HANDLING = 1;
    private const STATE_CLOSED = 2;

    protected array $data = [];

    /** @var callable|null */
    private mixed $submitHandler;

    /** @var callable|null */
    private mixed $closeHandler = null;

    private int $currentGeneration = 0;
    private array $trackingStates = [];

    /**
     * Creates a form with an optional submit handler.
     *
     * @param  callable|null $submitHandler
     */
    public function __construct(?callable $submitHandler = null) {
        $this->submitHandler = $submitHandler;
    }

    public function getCallable(): ?callable {
        return $this->submitHandler;
    }

    /**
     * Sets the callback executed after a valid form response is processed.
     *
     * @param  callable|null $callable
     *
     * @return static
     */
    public function setCallable(?callable $callable): static {
        $this->submitHandler = $callable;
        return $this;
    }

    public function onSubmit(?callable $handler): static {
        return $this->setCallable($handler);
    }

    /**
     * Sets the callback executed when the form is closed by the player.
     *
     * @param  callable|null $handler
     *
     * @return static
     */
    public function onClose(?callable $handler): static {
        $this->closeHandler = $handler;
        return $this;
    }

    public function setTitle(string $title): static {
        $this->data['title'] = $title;
        return $this;
    }

    public function getTitle(): string {
        return (string) ($this->data['title'] ?? '');
    }

    /**
     * Checks whether this form generation can still accept a player response.
     *
     * @param  Player $player
     *
     * @return bool
     */
    public function isOpenFor(Player $player): bool {
        $state = $this->trackingStates[$this->playerId($player)] ?? null;

        return $state === null
            || $state['generation'] !== $this->currentGeneration
            || $state['state'] === self::STATE_OPEN;
    }

    public function isClosedFor(Player $player): bool {
        return !$this->isOpenFor($player);
    }

    /**
     * Handles a raw PocketMine form response once and closes the current generation.
     *
     * @param  Player $player
     * @param  mixed  $data
     *
     * @return void
     */
    final public function handleResponse(Player $player, mixed $data): void {
        $generation = $this->beginHandling($player);
        if ($generation === null) {
            return;
        }

        try {
            if ($data === null) {
                ($this->closeHandler)($player);
                return;
            }

            $response = $this->processData($data);
            $this->handleProcessedResponse($player, $response, $data);
        } finally {
            $this->closeHandling($player, $generation);
        }
    }

    /**
     * Converts raw form data into the value passed to the submit handler.
     *
     * @param  mixed $data
     *
     * @return mixed
     */
    protected function processData(mixed $data): mixed {
        return $data;
    }

    /**
     * Executes the submit handler with processed and raw response data.
     *
     * @param  Player $player
     * @param  mixed  $response
     * @param  mixed  $rawData
     *
     * @return void
     */
    protected function handleProcessedResponse(Player $player, mixed $response, mixed $rawData): void {
        if ($this->submitHandler !== null) {
            ($this->submitHandler)($player, $response, $rawData, $this);
        }
    }

    /**
     * Serializes form data and advances the tracking generation.
     *
     * @return array
     */
    public function jsonSerialize(): array {
        $this->currentGeneration++;
        $this->pruneStaleTrackingStates();

        return $this->data;
    }

    private function beginHandling(Player $player): ?int {
        $id = $this->playerId($player);
        $generation = $this->currentGeneration;
        $state = $this->trackingStates[$id] ?? null;

        $alreadyHandledOrClosed = $state !== null
            && $state['generation'] === $generation
            && $state['state'] !== self::STATE_OPEN;

        if ($alreadyHandledOrClosed) {
            return null;
        }

        $this->trackingStates[$id] = [
            'generation' => $generation,
            'state' => self::STATE_HANDLING,
        ];

        return $generation;
    }

    private function closeHandling(Player $player, int $generation): void {
        $this->trackingStates[$this->playerId($player)] = [
            'generation' => $generation,
            'state' => self::STATE_CLOSED,
        ];
    }

    private function playerId(Player $player): string {
        return $player->getUniqueId()->toString();
    }

    private function pruneStaleTrackingStates(): void {
        $cutoff = $this->currentGeneration - 1;

        foreach ($this->trackingStates as $id => $state) {
            if ($state['generation'] < $cutoff) {
                unset($this->trackingStates[$id]);
            }
        }
    }
}
