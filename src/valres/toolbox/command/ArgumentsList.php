<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use Traversable;

final class ArgumentsList implements IteratorAggregate, Countable , ArrayAccess {
    private readonly CommandSender $sender;
    private array $arguments = [];

    public function __construct(CommandSender $sender, array $arguments = []) {
        $this->sender = $sender;
        $this->arguments = $arguments;
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->arguments[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->arguments[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset === null) {
            throw new InvalidArgumentException("Argument must have a name");
        }
        $this->arguments[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->arguments[$offset]);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->arguments);
    }

    public function count(): int {
        return count($this->arguments);
    }

    public function toArray(): array {
        return $this->arguments;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->arguments[$key] ?? $default;
    }

    public function has(string $key): bool {
        return array_key_exists($key, $this->arguments);
    }

    public function string(string $key, ?string $default = null): ?string {
        $value = $this->get($key, $default);
        return $value === null ? null : (string) $value;
    }

    public function int(string $key, ?int $default = null): ?int {
        $value = $this->get($key, $default);
        return $value === null ? null : (int) $value;
    }

    public function bool(string $key, ?bool $default = null): ?bool {
        $value = $this->get($key, $default);
        return $value === null ? null : (bool) $value;
    }

    /** @return Entity[] */
    public function resolveTargets(callable|string $key = "target", ?callable $callback = null, bool $defaultToSender = true): array {
        if (is_callable($key)) {
            $callback = $key;
            $key = "target";
        }

        $target = $this->get($key);

        if ($target === null) {
            $targets = $defaultToSender && $this->sender instanceof Entity ? [$this->sender] : [];
        } elseif ($target instanceof Entity) {
            $targets = [$target];
        } elseif (is_array($target)) {
            $targets = array_values(array_filter($target, static fn(mixed $value): bool => $value instanceof Entity));
        } else {
            $targets = [];
        }

        if ($callback !== null) {
            foreach ($targets as $entity) {
                $callback($entity);
            }
        }

        return $targets;
    }

    public function sender(): CommandSender {
        return $this->sender;
    }
}
