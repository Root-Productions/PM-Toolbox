<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use valres\toolbox\form\attribute\FormField;
use valres\toolbox\form\attribute\FormTitle;
use valres\toolbox\form\exception\FormElementException;

final class FormFactory {
    private function __construct() {
    }

    /** @throws ReflectionException */
    public static function customFromAttributes(object|string $source, ?callable $callback = null): CustomForm {
        $reflection = new ReflectionClass($source);
        $form = new CustomForm($callback);

        $titleAttributes = $reflection->getAttributes(FormTitle::class);
        if ($titleAttributes !== []) {
            $form->setTitle($titleAttributes[0]->newInstance()->title);
        }

        $fields = [];
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(FormField::class);
            if ($attributes === []) {
                continue;
            }

            $fields[] = [$property, $attributes[0]->newInstance()];
        }

        usort(
            $fields,
            static fn(array $a, array $b): int => [$a[1]->order, $a[0]->getName()] <=> [$b[1]->order, $b[0]->getName()]
        );

        foreach ($fields as [$property, $field]) {
            self::addField($form, $source, $property, $field);
        }

        return $form;
    }

    private static function addField(CustomForm $form, object|string $source, ReflectionProperty $property, FormField $field): void {
        $name = $field->name ?? $property->getName();
        $text = $field->text !== "" ? $field->text : $property->getName();
        $default = self::readDefault($source, $property);

        match ($field->type) {
            FormField::LABEL => $form->addLabel($text, $name),
            FormField::HEADER => $form->addHeader($text, $name),
            FormField::DIVIDER => $form->addDivider($name),
            FormField::TOGGLE => $form->addToggle($text, is_bool($default) ? $default : null, $name),
            FormField::INPUT => $form->addInput($text, $field->placeholder, is_string($default) ? $default : null, $name),
            FormField::SLIDER => $form->addSlider(
                $text,
                $field->min ?? throw new FormElementException("Slider '{$name}' is missing min."),
                $field->max ?? throw new FormElementException("Slider '{$name}' is missing max."),
                $field->step,
                is_int($default) || is_float($default) ? $default : null,
                $name
            ),
            FormField::DROPDOWN => $form->addDropdown(
                $text,
                $field->options,
                self::resolveDefaultIndex($default, $field),
                $name,
                $field->returnOption
            ),
            FormField::STEP_SLIDER => $form->addStepSlider(
                $text,
                $field->options,
                self::resolveDefaultIndex($default, $field),
                $name,
                $field->returnOption
            ),
            default => throw new FormElementException("Unknown form field type '{$field->type}'.")
        };
    }

    private static function readDefault(object|string $source, ReflectionProperty $property): mixed {
        if (!is_object($source) || !$property->isInitialized($source)) {
            return null;
        }

        $property->setAccessible(true);
        return $property->getValue($source);
    }

    private static function resolveDefaultIndex(mixed $default, FormField $field): ?int {
        if ($field->defaultIndex !== null) {
            return $field->defaultIndex;
        }

        if (is_int($default) && isset($field->options[$default])) {
            return $default;
        }

        if (is_string($default)) {
            $index = array_search($default, $field->options, true);
            return is_int($index) ? $index : null;
        }

        return null;
    }
}
