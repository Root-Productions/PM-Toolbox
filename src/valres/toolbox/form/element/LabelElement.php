<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use valres\toolbox\form\exception\FormResponseException;

class LabelElement extends FormElement {
    public function expectsResponse(): bool {
        return false;
    }

    public function validate(mixed $value): void {
        if ($value !== null) {
            throw new FormResponseException("Expected null response for label '{$this->text}'.");
        }
    }

    public function jsonSerialize(): array {
        return ["type" => "label", "text" => $this->text];
    }
}
