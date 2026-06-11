<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

final class RepairItem implements ItemComponentValue {
    /** @param list<BlockDescriptor|string|array> $items */
    public function __construct(
        private readonly array $items,
        private readonly string|int $repairAmount
    ) {
    }

    public static function of(BlockDescriptor|string|array $items, string|int $repairAmount): self {
        if ($items instanceof BlockDescriptor || is_string($items)) {
            return new self([$items], $repairAmount);
        }

        return new self(array_values($items), $repairAmount);
    }

    public function toArray(): array {
        return [
            "items" => array_map(
                static fn(BlockDescriptor|string|array $item): string|array => $item instanceof BlockDescriptor ? $item->toArray() : $item,
                array_values($this->items)
            ),
            "repair_amount" => $this->repairAmount
        ];
    }
}
