<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

final class DduiDropdownItem {
    public function __construct(
        public string $label,
        public int|float $value,
        public ?string $description = null
    ) {
    }
}
