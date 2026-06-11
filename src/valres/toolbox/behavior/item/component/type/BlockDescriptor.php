<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

final class BlockDescriptor implements ItemComponentValue {
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

    public function toArray(): array {
        return array_filter([
            "name" => $this->name,
            "states" => $this->states,
            "tags" => $this->tags
        ], static fn(mixed $value): bool => $value !== null);
    }
}
