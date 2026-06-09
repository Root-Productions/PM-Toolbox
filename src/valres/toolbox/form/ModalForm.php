<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use pocketmine\player\Player;
use valres\toolbox\form\exception\FormResponseException;

class ModalForm extends BaseForm {
    private mixed $yesHandler = null;
    private mixed $noHandler = null;

    public function __construct(?callable $callback = null) {
        parent::__construct($callback);
        $this->data = [
            "type" => "modal",
            "title" => "",
            "content" => "",
            "button1" => "",
            "button2" => ""
        ];
    }

    public function getContent(): string {
        return (string) $this->data["content"];
    }

    public function setContent(string $content): static {
        $this->data["content"] = $content;
        return $this;
    }

    public function setButton1(string $text): static {
        $this->data["button1"] = $text;
        return $this;
    }

    public function getButton1(): string {
        return (string) $this->data["button1"];
    }

    public function setButton2(string $text): static {
        $this->data["button2"] = $text;
        return $this;
    }

    public function getButton2(): string {
        return (string) $this->data["button2"];
    }

    public function onYes(?callable $handler): static {
        $this->yesHandler = $handler;
        return $this;
    }

    public function onNo(?callable $handler): static {
        $this->noHandler = $handler;
        return $this;
    }

    protected function processData(mixed $data): mixed {
        if (!is_bool($data)) {
            throw new FormResponseException("Expected a boolean response, got " . gettype($data));
        }

        return $data;
    }

    protected function handleProcessedResponse(Player $player, mixed $response, mixed $rawData): void {
        $handler = $response ? $this->yesHandler : $this->noHandler;
        if (is_callable($handler)) {
            $handler($player, $response, $this);
            return;
        }

        parent::handleProcessedResponse($player, $response, $rawData);
    }
}
