<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use Closure;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueMap;
use pocketmine\player\Player;

final class DduiMessageBox extends AbstractDduiScreen {
    private ?string $button1Label = null;
    private ?string $button1Tooltip = null;
    private ?string $button2Label = null;
    private ?string $button2Tooltip = null;
    private int $selection = 0;

    /** @var (Closure(Player, int, self): void)|null */
    private ?Closure $selectionHandler = null;

    public function __construct(
        private string $title,
        private string $body = ""
    ) {}

    public static function create(string $title, string $body = ""): self {
        return new self($title, $body);
    }

    public function button1(string $label, ?string $tooltip = null): self {
        $this->button1Label = $label;
        $this->button1Tooltip = $tooltip;

        return $this;
    }

    public function button2(string $label, ?string $tooltip = null): self {
        $this->button2Label = $label;
        $this->button2Tooltip = $tooltip;

        return $this;
    }

    /**
     * @param Closure(Player, int, self): void $handler
     */
    public function onSelect(Closure $handler): self {
        $this->selectionHandler = $handler;

        return $this;
    }

    public function getScreenId(): string {
        return "minecraft:message_box";
    }

    public function serializeData(): DynamicValueMap {
        $data = [
            "body" => DduiValue::string($this->body),
            "title" => DduiValue::string($this->title),
        ];

        if ($this->button1Label !== null) {
            $data["button1"] = DduiElements::button($this->button1Label, $this->button1Tooltip);
        }

        if ($this->button2Label !== null) {
            $data["button2"] = DduiElements::button($this->button2Label, $this->button2Tooltip);
        }

        return DduiValue::map($data);
    }

    public function handleUpdate(Player $player, string $path, bool|string|float $value): bool {
        $this->selection = match ($path) {
            "button1.onClick" => 1,
            "button2.onClick" => 2,
            default => $this->selection,
        };

        return $path === "button1.onClick" || $path === "button2.onClick";
    }

    public function handleClose(Player $player, int $reason): void {
        parent::handleClose($player, $reason);

        if ($this->selectionHandler !== null) {
            ($this->selectionHandler)($player, $this->selection, $this);
        }
    }
}
