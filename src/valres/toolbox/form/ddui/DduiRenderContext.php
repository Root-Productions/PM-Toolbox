<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use valres\toolbox\form\ddui\packet\type\DduiBoolValue;
use valres\toolbox\form\ddui\packet\type\DduiDataStorePropertyValue;
use valres\toolbox\form\ddui\packet\type\DduiInt64Value;
use valres\toolbox\form\ddui\packet\type\DduiListValue;
use valres\toolbox\form\ddui\packet\type\DduiMapEntry;
use valres\toolbox\form\ddui\packet\type\DduiMapValue;
use valres\toolbox\form\ddui\packet\type\DduiNoneValue;
use valres\toolbox\form\ddui\packet\type\DduiStringValue;

final class DduiRenderContext {
    public function __construct(
        private DduiManager $manager,
        private string $playerUuid,
        private int $formId
    ) {
    }

    public function path(int $index, string $property): string {
        return "layout[{$index}].{$property}";
    }

    public function entry(string $key, DduiDataStorePropertyValue $value): DduiMapEntry {
        return new DduiMapEntry($key, $value);
    }

    public function bind(string $path, DduiObservable $observable): void {
        $this->manager->registerObservableBinding($this->playerUuid, $this->formId, $path, $observable);
    }

    public function click(string $path, callable $handler): void {
        $this->manager->registerClickHandler($this->playerUuid, $this->formId, $path, $handler);
    }

    public function bool(bool|DduiObservable|null $value, string $path, bool $default = false): bool {
        if ($value instanceof DduiObservable) {
            $this->bind($path, $value);
            return (bool) $value->get();
        }

        return $value ?? $default;
    }

    public function int(int|float|DduiObservable|null $value, string $path, int $default = 0): int {
        if ($value instanceof DduiObservable) {
            $this->bind($path, $value);
            return (int) $value->get();
        }

        return $value === null ? $default : (int) $value;
    }

    public function text(string|DduiObservable|null $value, string $path, string $default = ""): string {
        if ($value instanceof DduiObservable) {
            $this->bind($path, $value);
            return (string) $value->get();
        }

        return $value ?? $default;
    }

    public function value(mixed $value): DduiDataStorePropertyValue {
        if ($value === null) {
            return new DduiNoneValue();
        }

        if (is_bool($value)) {
            return new DduiBoolValue($value);
        }

        if (is_int($value) || is_float($value)) {
            return new DduiInt64Value((int) $value);
        }

        if (is_string($value)) {
            return new DduiStringValue($value);
        }

        if (is_array($value)) {
            $isList = array_keys($value) === range(0, count($value) - 1);
            if ($isList) {
                return new DduiListValue(array_map(fn(mixed $entry): DduiDataStorePropertyValue => $this->value($entry), $value));
            }

            $entries = [];
            foreach ($value as $key => $entryValue) {
                $entries[] = new DduiMapEntry((string) $key, $this->value($entryValue));
            }
            return new DduiMapValue($entries);
        }

        return new DduiNoneValue();
    }
}
