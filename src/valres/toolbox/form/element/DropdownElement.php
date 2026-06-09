<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use valres\toolbox\form\exception\FormElementException;
use valres\toolbox\form\exception\FormResponseException;

class DropdownElement extends FormElement {
    /** @param string[] $options */
    public function __construct(
        string $text,
        protected array $options,
        protected ?int $default = null,
        string|int|null $name = null,
        protected bool $returnOption = true
    ) {
        if ($options === []) {
            throw new FormElementException("Dropdown '{$text}' requires at least one option.");
        }
        parent::__construct($text, $name);
    }

    public function validate(mixed $value): void {
        if (!is_int($value) || !isset($this->options[$value])) {
            throw new FormResponseException("Invalid dropdown response for '{$this->text}'.");
        }
    }

    public function read(mixed $value): mixed {
        return $this->returnOption ? $this->options[$value] : $value;
    }

    public function jsonSerialize(): array {
        $data = ["type" => "dropdown", "text" => $this->text, "options" => array_values($this->options)];
        if ($this->default !== null) {
            $data["default"] = $this->default;
        }
        return $data;
    }
}
