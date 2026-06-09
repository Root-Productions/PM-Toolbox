<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\element;

use valres\toolbox\form\ddui\DduiObservable;
use valres\toolbox\form\ddui\DduiRenderContext;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;

final class DduiToggle implements DduiElement {
    public function __construct(
        private string|DduiObservable $label,
        private DduiObservable $toggled,
        private string|DduiObservable|null $description = null,
        private bool|DduiObservable|null $disabled = null,
        private bool|DduiObservable|null $visible = null
    ) {
    }

    public function build(DduiRenderContext $context, int $index): array {
        return [
            $context->entry("description", $context->value($context->text($this->description, $context->path($index, "description")))),
            $context->entry("disabled", new DduiBoolValue($context->bool($this->disabled, $context->path($index, "disabled")))),
            $context->entry("label", $context->value($context->text($this->label, $context->path($index, "label")))),
            $context->entry("toggle_visible", new DduiBoolValue(true)),
            $context->entry("toggled", new DduiBoolValue($context->bool($this->toggled, $context->path($index, "toggled")))),
            $context->entry("visible", new DduiBoolValue($context->bool($this->visible, $context->path($index, "visible"), true))),
        ];
    }
}
