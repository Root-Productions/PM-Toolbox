<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\element;

use valres\toolbox\form\ddui\DduiDropdownItem;
use valres\toolbox\form\ddui\DduiObservable;
use valres\toolbox\form\ddui\DduiRenderContext;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiInt64Value;
use valres\toolbox\form\ddui\packet\type\DduiMapEntry;
use valres\toolbox\form\ddui\packet\type\DduiMapValue;

final class DduiDropdown implements DduiElement {
    /** @param DduiDropdownItem[] $items */
    public function __construct(
        private string|DduiObservable $label,
        private DduiObservable $value,
        private array $items,
        private string|DduiObservable|null $description = null,
        private bool|DduiObservable|null $disabled = null,
        private bool|DduiObservable|null $visible = null
    ) {
    }

    public function build(DduiRenderContext $context, int $index): array {
        $items = [];
        foreach ($this->items as $itemIndex => $item) {
            $items[] = new DduiMapEntry((string) $itemIndex, new DduiMapValue([
                $context->entry("label", $context->value($item->label)),
                $context->entry("value", new DduiInt64Value((int) $item->value)),
                $context->entry("description", $context->value($item->description ?? "")),
            ]));
        }
        $items[] = new DduiMapEntry("length", new DduiInt64Value(count($this->items)));

        return [
            $context->entry("description", $context->value($context->text($this->description, $context->path($index, "description")))),
            $context->entry("disabled", new DduiBoolValue($context->bool($this->disabled, $context->path($index, "disabled")))),
            $context->entry("dropdown_visible", new DduiBoolValue(true)),
            $context->entry("items", new DduiMapValue($items)),
            $context->entry("label", $context->value($context->text($this->label, $context->path($index, "label")))),
            $context->entry("value", new DduiInt64Value($context->int($this->value, $context->path($index, "value")))),
            $context->entry("visible", new DduiBoolValue($context->bool($this->visible, $context->path($index, "visible"), true))),
        ];
    }
}
