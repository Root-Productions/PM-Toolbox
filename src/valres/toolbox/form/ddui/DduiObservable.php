<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

final class DduiObservable {
    /** @var array<int, callable> */
    private array $listeners = [];
    private int $nextListenerId = 1;

    public function __construct(
        private bool|float|int|string $value,
        private readonly bool $clientWritable = false
    ) {
    }

    public static function writable(bool|float|int|string $value): self {
        return new self($value, true);
    }

    public function get(): bool|float|int|string {
        return $this->value;
    }

    public function set(bool|float|int|string $value): void {
        if ($this->value === $value) {
            return;
        }

        $this->value = $value;
        foreach ($this->listeners as $listener) {
            $listener($value);
        }

        DduiManager::getInstance()?->notifyObservableChanged($this);
    }

    public function applyClientValue(bool|float|int|string $value): void {
        $this->value = $value;
        foreach ($this->listeners as $listener) {
            $listener($value);
        }
    }

    public function subscribe(callable $listener): callable {
        $id = $this->nextListenerId++;
        $this->listeners[$id] = $listener;

        return function() use ($id): void {
            unset($this->listeners[$id]);
        };
    }

    public function isClientWritable(): bool {
        return $this->clientWritable;
    }
}
