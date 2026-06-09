<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

class StepSliderElement extends DropdownElement {
    public function jsonSerialize(): array {
        $data = ["type" => "step_slider", "text" => $this->text, "steps" => array_values($this->options)];
        if ($this->default !== null) {
            $data["default"] = $this->default;
        }
        return $data;
    }
}
