<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use valres\toolbox\form\exception\FormElementException;
use valres\toolbox\form\exception\FormResponseException;

class SliderElement extends FormElement {
    public function __construct(
        string $text,
        protected float|int $min,
        protected float|int $max,
        protected float|int|null $step = null,
        protected float|int|null $default = null,
        string|int|null $name = null
    ) {
        if ($min > $max) {
            throw new FormElementException("Slider min cannot be greater than max.");
        }
        parent::__construct($text, $name);
    }

    public function validate(mixed $value): void {
        if ((!is_float($value) && !is_int($value)) || $value < $this->min || $value > $this->max) {
            throw new FormResponseException("Expected numeric response between {$this->min} and {$this->max} for '{$this->text}'.");
        }
    }

    public function jsonSerialize(): array {
        $data = ["type" => "slider", "text" => $this->text, "min" => $this->min, "max" => $this->max];
        if ($this->step !== null) {
            $data["step"] = $this->step;
        }
        if ($this->default !== null) {
            $data["default"] = $this->default;
        }
        return $data;
    }
}
