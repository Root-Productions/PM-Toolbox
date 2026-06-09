<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\element;

use valres\toolbox\form\ddui\DduiObservable;
use valres\toolbox\form\ddui\DduiRenderContext;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;

final class DduiDivider implements DduiElement {
    public function __construct(private bool|DduiObservable|null $visible = null) {
    }

    public function build(DduiRenderContext $context, int $index): array {
        return [
            $context->entry("divider_visible", new DduiBoolValue(true)),
            $context->entry("visible", new DduiBoolValue($context->bool($this->visible, $context->path($index, "visible"), true))),
        ];
    }
}
