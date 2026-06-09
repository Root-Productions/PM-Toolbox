<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use pocketmine\form\Form as PMMPForm;
use pocketmine\player\Player;

abstract class BaseForm implements PMMPForm {
    protected array $data = [];
    private mixed $submitHandler;
    private mixed $closeHandler = null;

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
        if ($data === null) {
            if (is_callable($this->closeHandler)) {
                ($this->closeHandler)($player);
            }
            return;
        }

        $response = $this->processData($data);
        $this->handleProcessedResponse($player, $response, $data);
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
        return $this->data;
    }
}
