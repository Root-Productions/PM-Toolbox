<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

class HeaderElement extends LabelElement {
    public function jsonSerialize(): array {
        return ["type" => "header", "text" => $this->text];
    }
}
