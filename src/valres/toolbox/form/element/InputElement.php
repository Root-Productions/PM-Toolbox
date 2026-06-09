<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use valres\toolbox\form\exception\FormResponseException;

class InputElement extends FormElement {
    public function __construct(
        string $text,
        protected string $placeholder = "",
        protected ?string $default = null,
        string|int|null $name = null
    ) {
        parent::__construct($text, $name);
    }

    public function validate(mixed $value): void {
        if (!is_string($value)) {
            throw new FormResponseException("Expected string response for '{$this->text}'.");
        }
    }

    public function jsonSerialize(): array {
        $data = ["type" => "input", "text" => $this->text, "placeholder" => $this->placeholder];
        if ($this->default !== null) {
            $data["default"] = $this->default;
        }
        return $data;
    }
}
