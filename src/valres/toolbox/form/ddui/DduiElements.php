<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueMap;

final class DduiElements {
    private function __construct() {}

    public static function label(string $text): DynamicValueMap {
        return DduiValue::map([
            "label_visible" => DduiValue::bool(true),
            "text" => DduiValue::string($text),
            "visible" => DduiValue::bool(true),
        ]);
    }

    public static function divider(): DynamicValueMap {
        return DduiValue::map([
            "divider_visible" => DduiValue::bool(true),
            "visible" => DduiValue::bool(true),
        ]);
    }

    public static function spacer(): DynamicValueMap {
        return DduiValue::map([
            "spacer_visible" => DduiValue::bool(true),
            "visible" => DduiValue::bool(true),
        ]);
    }

    public static function textField(string $label, string $value = "", string $description = ""): DynamicValueMap {
        return DduiValue::map([
            "description" => DduiValue::string($description),
            "label" => DduiValue::string($label),
            "text" => DduiValue::string($value),
            "textfield_visible" => DduiValue::bool(true),
            "visible" => DduiValue::bool(true),
        ]);
    }

    public static function toggle(string $label, bool $value = false): DynamicValueMap {
        return DduiValue::map([
            "label" => DduiValue::string($label),
            "toggle_visible" => DduiValue::bool(true),
            "toggled" => DduiValue::bool($value),
            "visible" => DduiValue::bool(true),
        ]);
    }

    /**
     * @param list<string> $options
     */
    public static function dropdown(string $label, array $options, int $defaultIndex = 0): DynamicValueMap {
        $options = array_values($options);

        if ($options === []) {
            throw new InvalidArgumentException("Dropdown options cannot be empty.");
        }

        if (!isset($options[$defaultIndex])) {
            throw new InvalidArgumentException("Dropdown default index {$defaultIndex} does not exist.");
        }

        $items = [];
        foreach (array_values($options) as $index => $optionLabel) {
            $items[(string) $index] = DduiValue::map([
                "label" => DduiValue::string($optionLabel),
                "value" => DduiValue::long($index),
            ]);
        }
        $items["length"] = DduiValue::long(count($options));

        return DduiValue::map([
            "dropdown_visible" => DduiValue::bool(true),
            "items" => DduiValue::map($items),
            "label" => DduiValue::string($label),
            "value" => DduiValue::long($defaultIndex),
            "visible" => DduiValue::bool(true),
        ]);
    }

    public static function slider(
        string $label,
        float $value,
        float $min,
        float $max,
        float $step = 1.0,
        string $description = ""
    ): DynamicValueMap {
        if ($min > $max) {
            throw new InvalidArgumentException("Slider minimum cannot be greater than maximum.");
        }

        if ($step <= 0.0) {
            throw new InvalidArgumentException("Slider step must be greater than zero.");
        }

        return DduiValue::map([
            "description" => DduiValue::string($description),
            "label" => DduiValue::string($label),
            "maxValue" => DduiValue::double($max),
            "minValue" => DduiValue::double($min),
            "slider_visible" => DduiValue::bool(true),
            "step" => DduiValue::double($step),
            "value" => DduiValue::double(max($min, min($max, $value))),
            "visible" => DduiValue::bool(true),
        ]);
    }

    public static function button(string $label, ?string $tooltip = null): DynamicValueMap {
        $data = [
            "button_visible" => DduiValue::bool(true),
            "label" => DduiValue::string($label),
            "onClick" => DduiValue::long(0),
            "visible" => DduiValue::bool(true),
        ];

        if ($tooltip !== null) {
            $data["tooltip"] = DduiValue::string($tooltip);
            $data["tooltip_visible"] = DduiValue::bool(true);
        }

        return DduiValue::map($data);
    }
}
