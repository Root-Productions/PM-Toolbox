<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUICloseScreenPacket;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUIShowScreenPacket;
use pocketmine\player\Player;
use valres\toolbox\form\ddui\element\DduiButton;
use valres\toolbox\form\ddui\element\DduiDivider;
use valres\toolbox\form\ddui\element\DduiDropdown;
use valres\toolbox\form\ddui\element\DduiElement;
use valres\toolbox\form\ddui\element\DduiHeader;
use valres\toolbox\form\ddui\element\DduiLabel;
use valres\toolbox\form\ddui\element\DduiSlider;
use valres\toolbox\form\ddui\element\DduiSpacer;
use valres\toolbox\form\ddui\element\DduiTextField;
use valres\toolbox\form\ddui\element\DduiToggle;
use valres\toolbox\form\ddui\packet\DduiDataStorePacket;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiDataStoreChange;

final class DduiForm {
    private ?int $formId = null;
    private bool $showing = false;
    private bool $closeButton = false;
    private mixed $closeHandler = null;
    private DduiPayloadComposer $composer;

    /** @var DduiElement[] */
    private array $elements = [];

    public function __construct(
        private readonly Player $player,
        private string|DduiObservable $title
    ) {
        $this->composer = new DduiPayloadComposer();
    }

    public static function create(Player $player, string|DduiObservable $title): self {
        return new self($player, $title);
    }

    public function closeButton(bool $enabled = true): self {
        $this->closeButton = $enabled;
        return $this;
    }

    public function onClose(?callable $handler): self {
        $this->closeHandler = $handler;
        return $this;
    }

    public function add(DduiElement $element): self {
        $this->elements[] = $element;
        return $this;
    }

    public function button(string|DduiObservable $label, callable $onClick, bool|DduiObservable|null $disabled = null, string|DduiObservable|null $tooltip = null, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiButton($label, $onClick, $disabled, $tooltip, $visible));
    }

    public function label(string|DduiObservable $text, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiLabel($text, $visible));
    }

    public function header(string|DduiObservable $text, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiHeader($text, $visible));
    }

    public function divider(bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiDivider($visible));
    }

    public function spacer(bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiSpacer($visible));
    }

    public function toggle(string|DduiObservable $label, DduiObservable $value, string|DduiObservable|null $description = null, bool|DduiObservable|null $disabled = null, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiToggle($label, $value, $description, $disabled, $visible));
    }

    public function textField(string|DduiObservable $label, DduiObservable $text, string|DduiObservable|null $description = null, bool|DduiObservable|null $disabled = null, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiTextField($label, $text, $description, $disabled, $visible));
    }

    public function slider(string|DduiObservable $label, DduiObservable $value, int|float|DduiObservable $min, int|float|DduiObservable $max, string|DduiObservable|null $description = null, bool|DduiObservable|null $disabled = null, int|float|DduiObservable|null $step = null, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiSlider($label, $value, $min, $max, $description, $disabled, $step, $visible));
    }

    /** @param DduiDropdownItem[] $items */
    public function dropdown(string|DduiObservable $label, DduiObservable $value, array $items, string|DduiObservable|null $description = null, bool|DduiObservable|null $disabled = null, bool|DduiObservable|null $visible = null): self {
        return $this->add(new DduiDropdown($label, $value, $items, $description, $disabled, $visible));
    }

    public function show(): void {
        if ($this->showing) {
            throw new DduiException("DDUI form is already open.");
        }

        $manager = DduiManager::getOrCreate();
        $playerUuid = $this->player->getUniqueId()->getBytes();
        if ($manager->hasActiveForm($playerUuid)) {
            throw new DduiException("Another DDUI form is already active for this player.");
        }

        $formId = $manager->nextFormId();
        $this->formId = $formId;

        $this->player->getNetworkSession()->sendDataPacket(ClientboundDataDrivenUIShowScreenPacket::create("minecraft:custom_form", $formId, null));

        $payload = $this->composer->compose($manager, $playerUuid, $formId, $this->title, $this->closeButton, $this->elements);
        $updateCount = $manager->nextUpdateCountFor($playerUuid);
        $this->player->getNetworkSession()->sendDataPacket(DduiDataStorePacket::create([
            new DduiDataStoreChange("minecraft", "custom_form_data", $updateCount, $payload),
            new DduiDataStoreChange("minecraft", "ddui_form_active", $updateCount, new DduiBoolValue(true)),
        ]));

        $this->showing = true;
        $manager->registerForm($playerUuid, $formId, $this);
    }

    public function close(): void {
        if (!$this->showing || $this->formId === null) {
            throw new DduiException("DDUI form is not open.");
        }

        $this->player->getNetworkSession()->sendDataPacket(ClientboundDataDrivenUICloseScreenPacket::create($this->formId));
        $this->showing = false;
    }

    public function isShowing(): bool {
        return $this->showing;
    }

    public function markClosed(): void {
        $this->showing = false;
        if (is_callable($this->closeHandler)) {
            ($this->closeHandler)($this->player, $this);
        }
    }
}
