<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use pocketmine\player\Player;
use valres\toolbox\form\element\ButtonElement;
use valres\toolbox\form\exception\FormResponseException;

class SimpleForm extends BaseForm {
    public const IMAGE_TYPE_PATH = 0;
    public const IMAGE_TYPE_URL = 1;

    /** @var ButtonElement[] */
    private array $buttons = [];

    public function __construct(?callable $callback = null) {
        parent::__construct($callback);
        $this->data = [
            "type" => "form",
            "title" => "",
            "content" => "",
            "buttons" => []
        ];
    }

    public function getContent(): string {
        return (string) $this->data["content"];
    }

    public function setContent(string $content): static {
        $this->data["content"] = $content;
        return $this;
    }

    public function addElement(ButtonElement $button): static {
        $this->buttons[] = $button;
        $this->data["buttons"][] = $button->jsonSerialize();
        return $this;
    }

    public function addSimpleButton(string $text, int $imageType = -1, string $imagePath = "", string|int|null $label = null): static {
        $type = match ($imageType) {
            self::IMAGE_TYPE_PATH => ButtonElement::IMAGE_TYPE_PATH,
            self::IMAGE_TYPE_URL => ButtonElement::IMAGE_TYPE_URL,
            default => null
        };

        return $this->addElement(new ButtonElement(
            $text,
            $label ?? count($this->buttons),
            $type,
            $type !== null ? $imagePath : null
        ));
    }

    public function addButton(
        string $text,
        int $imageType = self::IMAGE_TYPE_PATH,
        string $imagePath = "textures/items/stick",
        string|int|null $label = null,
        ?callable $callbackButton = null
    ): static {
        $type = $imageType === self::IMAGE_TYPE_URL ? ButtonElement::IMAGE_TYPE_URL : ButtonElement::IMAGE_TYPE_PATH;
        return $this->addElement(new ButtonElement($text, $label ?? count($this->buttons), $type, $imagePath, $callbackButton));
    }

    protected function processData(mixed $data): mixed {
        if (!is_int($data)) {
            throw new FormResponseException("Expected an integer response, got " . gettype($data));
        }

        if (!isset($this->buttons[$data])) {
            throw new FormResponseException("Button {$data} does not exist.");
        }

        return $this->buttons[$data]->getName();
    }

    protected function handleProcessedResponse(Player $player, mixed $response, mixed $rawData): void {
        if (is_int($rawData) && isset($this->buttons[$rawData])) {
            $callback = $this->buttons[$rawData]->getCallback();
            if ($callback !== null) {
                $callback($player, $response, $rawData, $this);
                return;
            }
        }

        parent::handleProcessedResponse($player, $response, $rawData);
    }
}
