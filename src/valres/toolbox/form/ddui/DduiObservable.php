<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use Closure;
use InvalidArgumentException;

final class DduiObservable {
    public const TYPE_STRING = "string";
    public const TYPE_BOOL = "bool";
    public const TYPE_NUMBER = "number";

    /** @var list<Closure(bool|string|float): void> */
    private array $subscribers = [];

    private function __construct(
        private bool|string|float $value,
        private string $type,
        private bool $clientWritable = false
    ) {}

    public static function string(string $value = "", bool $clientWritable = false): self {
        return new self($value, self::TYPE_STRING, $clientWritable);
    }

    public static function bool(bool $value = false, bool $clientWritable = false): self {
        return new self($value, self::TYPE_BOOL, $clientWritable);
    }

    public static function number(float|int $value = 0.0, bool $clientWritable = false): self {
        return new self((float) $value, self::TYPE_NUMBER, $clientWritable);
    }

    public function get(): bool|string|float {
        return $this->value;
    }

    public function getString(): string {
        $this->assertType(self::TYPE_STRING);

        return (string) $this->value;
    }

    public function getBool(): bool {
        $this->assertType(self::TYPE_BOOL);

        return (bool) $this->value;
    }

    public function getNumber(): float {
        $this->assertType(self::TYPE_NUMBER);

        return (float) $this->value;
    }

    public function set(bool|string|float|int $value, bool $notify = true): self {
        $value = is_int($value) ? (float) $value : $value;
        $this->assertValue($value);

        if ($this->value === $value) {
            return $this;
        }

        $this->value = $value;
        if ($notify) {
            foreach ($this->subscribers as $subscriber) {
                $subscriber($this->value);
            }
        }

        return $this;
    }

    public function isClientWritable(): bool {
        return $this->clientWritable;
    }

    public function getType(): string {
        return $this->type;
    }

    /**
     * @param Closure(bool|string|float): void $subscriber
     */
    public function subscribe(Closure $subscriber): self {
        $this->subscribers[] = $subscriber;

        return $this;
    }

    private function assertType(string $type): void {
        if ($this->type !== $type) {
            throw new InvalidArgumentException("Expected {$type} observable, got {$this->type}.");
        }
    }

    private function assertValue(bool|string|float $value): void {
        $valid = match ($this->type) {
            self::TYPE_STRING => is_string($value),
            self::TYPE_BOOL => is_bool($value),
            self::TYPE_NUMBER => is_float($value),
            default => false,
        };

        if (!$valid) {
            throw new InvalidArgumentException("Value does not match {$this->type} observable.");
        }
    }
}
