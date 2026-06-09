<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use JsonSerializable;
use valres\toolbox\form\exception\FormResponseException;

abstract class FormElement implements JsonSerializable {
    public function __construct(
        protected string $text,
        protected string|int|null $name = null
    ) {
    }

    public function getName(): string|int|null {
        return $this->name;
    }

    public function setName(string|int|null $name): static {
        $this->name = $name;
        return $this;
    }

    public function getText(): string {
        return $this->text;
    }

    public function setText(string $text): static {
        $this->text = $text;
        return $this;
    }

    public function expectsResponse(): bool {
        return true;
    }

    /** @throws FormResponseException */
    public function validate(mixed $value): void {
    }

    public function read(mixed $value): mixed {
        return $value;
    }
}
