<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits\type;

final class BlockDescriptor {
    /** @param array<string, bool|int|float|string>|null $states */
    public function __construct(
        private readonly ?string $name = null,
        private readonly ?array $states = null,
        private readonly ?string $tags = null
    ) {
    }

    public static function named(string $name, ?array $states = null): self {
        return new self(name: $name, states: $states);
    }

    public static function tagged(string $query): self {
        return new self(tags: $query);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return array_filter([
            "name" => $this->name,
            "states" => $this->states,
            "tags" => $this->tags
        ], static fn(mixed $value): bool => $value !== null);
    }

    /** @param list<BlockDescriptor|string|array> $descriptors */
    public static function listToArray(array $descriptors): array {
        return array_map(
            static fn(BlockDescriptor|string|array $descriptor): string|array => $descriptor instanceof BlockDescriptor ? $descriptor->toArray() : $descriptor,
            array_values($descriptors)
        );
    }
}
