<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

final class Ammunition implements ItemComponentValue {
    public function __construct(
        private readonly string $item,
        private readonly ?bool $searchInventory = null,
        private readonly ?bool $useInCreative = null,
        private readonly ?bool $useOffhand = null
    ) {
    }

    public function toArray(): array {
        return array_filter([
            "item" => $this->item,
            "search_inventory" => $this->searchInventory,
            "use_in_creative" => $this->useInCreative,
            "use_offhand" => $this->useOffhand
        ], static fn(mixed $value): bool => $value !== null);
    }
}
