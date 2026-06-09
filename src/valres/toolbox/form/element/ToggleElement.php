<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use valres\toolbox\form\exception\FormResponseException;

class ToggleElement extends FormElement {
    public function __construct(string $text, protected ?bool $default = null, protected ?string $tooltip = null, string|int|null $name = null) {
        parent::__construct($text, $name);
    }

    public function validate(mixed $value): void {
        if (!is_bool($value)) {
            throw new FormResponseException("Expected boolean response for '{$this->text}'.");
        }
    }

    public function jsonSerialize(): array {
        $data = ["type" => "toggle", "text" => $this->text];
        if ($this->default !== null) {
            $data["default"] = $this->default;
        }
        if ($this->tooltip !== null) {
            $data["tooltip"] = $this->default;
        }
        return $data;
    }
}
