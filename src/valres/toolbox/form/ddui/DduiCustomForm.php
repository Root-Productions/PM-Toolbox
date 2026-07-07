<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use Closure;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueMap;
use pocketmine\player\Player;

final class DduiCustomForm extends AbstractDduiScreen {
    /**
     * @var list<array{render: Closure(): DynamicValueMap, onUpdate: (Closure(Player, string, bool|string|float, self): bool)|null}>
     */
    private array $elements = [];

    /** @var (Closure(Player, bool|string|float, self): bool)|null */
    private ?Closure $closeButtonHandler = null;

    private bool $hasCloseButton = false;

    public function __construct(private string $title) {}

    public static function create(string $title): self {
        return new self($title);
    }

    public function withCloseButton(?Closure $onClick = null): self {
        $this->hasCloseButton = true;
        $this->closeButtonHandler = $onClick;

        return $this;
    }

    public function label(string|DduiObservable $text): self {
        $observable = $text instanceof DduiObservable ? $text : null;
        if ($observable !== null) {
            $observable->getString();
            $this->bind($observable);
        }

        return $this->add(fn(): DynamicValueMap => DduiElements::label($observable?->getString() ?? $text));
    }

    public function divider(): self {
        return $this->add(fn(): DynamicValueMap => DduiElements::divider());
    }

    public function spacer(): self {
        return $this->add(fn(): DynamicValueMap => DduiElements::spacer());
    }

    /**
     * @param (Closure(Player, string, self): void)|null $onChange
     */
    public function textField(string $label, string|DduiObservable $default, ?Closure $onChange = null, string $description = ""): self {
        $observable = $default instanceof DduiObservable ? $default : null;
        if ($observable !== null) {
            $observable->getString();
            $this->bind($observable);
        }

        return $this->add(
            fn(): DynamicValueMap => DduiElements::textField($label, $observable?->getString() ?? $default, $description),
            function(Player $player, string $property, bool|string|float $value) use ($onChange, $observable): bool {
                if ($property === "text" && is_string($value)) {
                    $this->writeObservable($observable, $value);
                    if ($onChange !== null) {
                        $onChange($player, $value, $this);
                    }
                }

                return false;
            }
        );
    }

    /**
     * @param (Closure(Player, bool, self): void)|null $onToggle
     */
    public function toggle(string $label, bool|DduiObservable $default, ?Closure $onToggle = null): self {
        $observable = $default instanceof DduiObservable ? $default : null;
        if ($observable !== null) {
            $observable->getBool();
            $this->bind($observable);
        }

        return $this->add(
            fn(): DynamicValueMap => DduiElements::toggle($label, $observable?->getBool() ?? $default),
            function(Player $player, string $property, bool|string|float $value) use ($onToggle, $observable): bool {
                if ($property === "toggled" && is_bool($value)) {
                    $this->writeObservable($observable, $value);
                    if ($onToggle !== null) {
                        $onToggle($player, $value, $this);
                    }
                }

                return false;
            }
        );
    }

    /**
     * @param list<string> $options
     * @param (Closure(Player, int, self): void)|null $onSelect
     */
    public function dropdown(string $label, array $options, int|DduiObservable $defaultIndex, ?Closure $onSelect = null): self {
        $observable = $defaultIndex instanceof DduiObservable ? $defaultIndex : null;
        if ($observable !== null) {
            $observable->getNumber();
            $this->bind($observable);
        }

        return $this->add(
            fn(): DynamicValueMap => DduiElements::dropdown($label, $options, (int) ($observable?->getNumber() ?? $defaultIndex)),
            function(Player $player, string $property, bool|string|float $value) use ($onSelect, $observable): bool {
                if ($property === "value" && is_float($value)) {
                    $this->writeObservable($observable, $value);
                    if ($onSelect !== null) {
                        $onSelect($player, (int) $value, $this);
                    }
                }

                return false;
            }
        );
    }

    /**
     * @param (Closure(Player, float, self): void)|null $onChange
     */
    public function slider(
        string $label,
        float|DduiObservable $default,
        float $min,
        float $max,
        float $step,
        ?Closure $onChange = null,
        string $description = ""
    ): self {
        $observable = $default instanceof DduiObservable ? $default : null;
        if ($observable !== null) {
            $observable->getNumber();
            $this->bind($observable);
        }

        return $this->add(
            fn(): DynamicValueMap => DduiElements::slider($label, (float) ($observable?->getNumber() ?? $default), $min, $max, $step, $description),
            function(Player $player, string $property, bool|string|float $value) use ($onChange, $observable): bool {
                if ($property === "value" && is_float($value)) {
                    $this->writeObservable($observable, $value);
                    if ($onChange !== null) {
                        $onChange($player, $value, $this);
                    }
                }

                return false;
            }
        );
    }

    /**
     * @param Closure(Player, self): void $onClick
     */
    public function button(string $label, Closure $onClick, bool $closeOnClick = true): self {
        return $this->add(
            fn(): DynamicValueMap => DduiElements::button($label),
            function(Player $player, string $property) use ($onClick, $closeOnClick): bool {
                if ($property === "onClick") {
                    $onClick($player, $this);

                    return $closeOnClick;
                }

                return false;
            }
        );
    }

    public function getScreenId(): string {
        return "minecraft:custom_form";
    }

    public function serializeData(): DynamicValueMap {
        $layout = [];
        foreach ($this->elements as $index => $element) {
            $layout[(string) $index] = ($element["render"])();
        }
        $layout["length"] = DduiValue::long(count($this->elements));

        $data = [
            "layout" => DduiValue::map($layout),
            "title" => DduiValue::string($this->title),
        ];

        if ($this->hasCloseButton) {
            $data["closeButton"] = DduiElements::button("Close");
        }

        return DduiValue::map($data);
    }

    public function handleUpdate(Player $player, string $path, bool|string|float $value): bool {
        if (preg_match('/^layout\[(\d+)]\.(.+)$/', $path, $matches) === 1) {
            $index = (int) $matches[1];
            $property = $matches[2];
            $onUpdate = $this->elements[$index]["onUpdate"] ?? null;

            return $onUpdate !== null && $onUpdate($player, $property, $value, $this);
        }

        if ($path === "closeButton.onClick") {
            if ($this->closeButtonHandler !== null) {
                return ($this->closeButtonHandler)($player, $value, $this);
            }

            return true;
        }

        return false;
    }

    /**
     * @param (Closure(Player, string, bool|string|float, self): bool)|null $onUpdate
     */
    private function add(Closure $render, ?Closure $onUpdate = null): self {
        $this->elements[] = [
            "render" => $render,
            "onUpdate" => $onUpdate,
        ];

        return $this;
    }

    private function bind(DduiObservable $observable): void {
        $observable->subscribe(function(): void {
            DduiManager::refreshScreen($this);
        });
    }

    private function writeObservable(?DduiObservable $observable, bool|string|float $value): void {
        if (!$observable instanceof DduiObservable || !$observable->isClientWritable()) {
            return;
        }

        $observable->set($value, false);
    }
}
