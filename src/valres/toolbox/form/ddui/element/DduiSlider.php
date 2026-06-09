<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\element;

use valres\toolbox\form\ddui\DduiObservable;
use valres\toolbox\form\ddui\DduiRenderContext;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiInt64Value;

final class DduiSlider implements DduiElement {
    public function __construct(
        private string|DduiObservable $label,
        private DduiObservable $value,
        private int|float|DduiObservable $min,
        private int|float|DduiObservable $max,
        private string|DduiObservable|null $description = null,
        private bool|DduiObservable|null $disabled = null,
        private int|float|DduiObservable|null $step = null,
        private bool|DduiObservable|null $visible = null
    ) {
    }

    public function build(DduiRenderContext $context, int $index): array {
        return [
            $context->entry("description", $context->value($context->text($this->description, $context->path($index, "description")))),
            $context->entry("disabled", new DduiBoolValue($context->bool($this->disabled, $context->path($index, "disabled")))),
            $context->entry("label", $context->value($context->text($this->label, $context->path($index, "label")))),
            $context->entry("maxValue", new DduiInt64Value($context->int($this->max, $context->path($index, "maxValue"), 100))),
            $context->entry("minValue", new DduiInt64Value($context->int($this->min, $context->path($index, "minValue")))),
            $context->entry("slider_visible", new DduiBoolValue(true)),
            $context->entry("step", new DduiInt64Value($context->int($this->step, $context->path($index, "step"), 1))),
            $context->entry("value", new DduiInt64Value($context->int($this->value, $context->path($index, "value")))),
            $context->entry("visible", new DduiBoolValue($context->bool($this->visible, $context->path($index, "visible"), true))),
        ];
    }
}
