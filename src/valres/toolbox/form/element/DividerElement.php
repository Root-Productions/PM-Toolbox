<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

class DividerElement extends LabelElement {
    public function __construct(string|int|null $name = null) {
        parent::__construct("", $name);
    }

    public function jsonSerialize(): array {
        return ["type" => "divider", "text" => ""];
    }
}
