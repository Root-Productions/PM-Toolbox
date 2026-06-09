<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use valres\toolbox\form\element\DividerElement;
use valres\toolbox\form\element\DropdownElement;
use valres\toolbox\form\element\FormElement;
use valres\toolbox\form\element\HeaderElement;
use valres\toolbox\form\element\InputElement;
use valres\toolbox\form\element\LabelElement;
use valres\toolbox\form\element\SliderElement;
use valres\toolbox\form\element\StepSliderElement;
use valres\toolbox\form\element\ToggleElement;
use valres\toolbox\form\exception\FormResponseException;

class CustomForm extends BaseForm {
    /** @var FormElement[] */
    private array $elements = [];

    public function __construct(?callable $callback = null) {
        parent::__construct($callback);
        $this->data = [
            "type" => "custom_form",
            "title" => "",
            "content" => []
        ];
    }

    public static function fromAttributes(object|string $source, ?callable $callback = null): self {
        return FormFactory::customFromAttributes($source, $callback);
    }

    public function addElement(FormElement $element): static {
        $this->elements[] = $element;
        $this->data["content"][] = $element->jsonSerialize();
        return $this;
    }

    public function getElements(): array {
        return $this->elements;
    }

    public function addLabel(string $text, string|int|null $label = null): static {
        return $this->addElement(new LabelElement($text, $label ?? count($this->elements)));
    }

    public function addHeader(string $text, string|int|null $label = null): static {
        return $this->addElement(new HeaderElement($text, $label ?? count($this->elements)));
    }

    public function addDivider(string|int|null $label = null): static {
        return $this->addElement(new DividerElement($label ?? count($this->elements)));
    }

    public function addToggle(string $text, ?bool $default = null, string|int|null $label = null): static {
        return $this->addElement(new ToggleElement($text, $default, $label ?? count($this->elements)));
    }

    public function addSlider(string $text, int|float $min, int|float $max, int|float|null $step = null, int|float|null $default = null, string|int|null $label = null): static {
        return $this->addElement(new SliderElement($text, $min, $max, $step, $default, $label ?? count($this->elements)));
    }

    public function addStepSlider(string $text, array $steps, ?int $defaultIndex = null, string|int|null $label = null, bool $returnOption = true): static {
        return $this->addElement(new StepSliderElement($text, $steps, $defaultIndex, $label ?? count($this->elements), $returnOption));
    }

    public function addDropdown(string $text, array $options, ?int $default = null, string|int|null $label = null, bool $returnOption = true): static {
        return $this->addElement(new DropdownElement($text, $options, $default, $label ?? count($this->elements), $returnOption));
    }

    public function addInput(string $text, string $placeholder = "", ?string $default = null, string|int|null $label = null): static {
        return $this->addElement(new InputElement($text, $placeholder, $default, $label ?? count($this->elements)));
    }

    protected function processData(mixed $data): mixed {
        if (!is_array($data)) {
            throw new FormResponseException("Expected an array response, got " . gettype($data));
        }

        if (count($data) !== count($this->elements)) {
            throw new FormResponseException("Expected " . count($this->elements) . " response value(s), got " . count($data) . ".");
        }

        $response = [];
        foreach ($this->elements as $index => $element) {
            $value = $data[$index] ?? null;
            $element->validate($value);
            $response[$element->getName() ?? $index] = $element->read($value);
        }

        return $response;
    }
}
