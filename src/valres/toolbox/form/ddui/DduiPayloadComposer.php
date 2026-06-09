<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use valres\toolbox\form\ddui\element\DduiElement;
use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiInt64Value;
use valres\toolbox\form\ddui\packet\type\DduiMapEntry;
use valres\toolbox\form\ddui\packet\type\DduiMapValue;

final class DduiPayloadComposer {
    /** @param DduiElement[] $elements */
    public function compose(
        DduiManager $manager,
        string $playerUuid,
        int $formId,
        string|DduiObservable $title,
        bool $closeButton,
        array $elements
    ): DduiMapValue {
        $context = new DduiRenderContext($manager, $playerUuid, $formId);
        $layout = [];

        foreach ($elements as $index => $element) {
            $entries = $element->build($context, $index);
            $visible = true;
            $disabled = false;

            foreach ($entries as $entry) {
                $value = $entry->getValue();
                if (!$value instanceof DduiBoolValue) {
                    continue;
                }
                if ($entry->getKey() === "visible") {
                    $visible = $value->getValue();
                } elseif ($entry->getKey() === "disabled") {
                    $disabled = $value->getValue();
                }
            }

            $manager->registerElementState($playerUuid, $formId, $index, $visible, $disabled);
            $layout[] = new DduiMapEntry((string) $index, new DduiMapValue($entries));
        }

        $layout[] = new DduiMapEntry("length", new DduiInt64Value(count($elements)));

        return new DduiMapValue([
            new DduiMapEntry("closeButton", new DduiMapValue([
                new DduiMapEntry("button_visible", new DduiBoolValue($closeButton)),
                new DduiMapEntry("label", $context->value("Close")),
                new DduiMapEntry("onClick", new DduiInt64Value(0)),
            ])),
            new DduiMapEntry("layout", new DduiMapValue($layout)),
            new DduiMapEntry("title", $context->value($context->text($title, "title"))),
        ]);
    }
}
