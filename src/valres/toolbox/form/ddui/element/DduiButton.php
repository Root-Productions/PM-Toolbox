<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\element;

use valres\toolbox\form\ddui\DduiObservable;
use valres\toolbox\form\ddui\DduiRenderContext;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiInt64Value;

final class DduiButton implements DduiElement {
    public function __construct(
        private readonly string|DduiObservable $label,
        private readonly mixed $onClick,
        private readonly bool|DduiObservable|null $disabled = null,
        private readonly string|DduiObservable|null $tooltip = null,
        private readonly bool|DduiObservable|null $visible = null
    ) {
    }

    public function build(DduiRenderContext $context, int $index): array {
        $context->click($context->path($index, "onClick"), function() use ($context): void {
            if (is_callable($this->onClick)) {
                ($this->onClick)();
            }
        });

        return [
            $context->entry("button_visible", new DduiBoolValue(true)),
            $context->entry("disabled", new DduiBoolValue($context->bool($this->disabled, $context->path($index, "disabled")))),
            $context->entry("label", $context->value($context->text($this->label, $context->path($index, "label")))),
            $context->entry("onClick", new DduiInt64Value(0)),
            $context->entry("tooltip", $context->value($context->text($this->tooltip, $context->path($index, "tooltip")))),
            $context->entry("visible", new DduiBoolValue($context->bool($this->visible, $context->path($index, "visible"), true))),
        ];
    }
}
