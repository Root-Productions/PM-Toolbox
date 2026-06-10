<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class RepairableComponent extends DataDrivenItemComponent {
    /** @param array<int, array{items: string[], repair_amount: string|int}> $repairItems */
    public function __construct(private readonly array $repairItems) {
    }

    public static function identifier(): string {
        return "minecraft:repairable";
    }

    public static function repairItem(array|string $items, string|int $repairAmount): array {
        return [
            "items" => is_array($items) ? array_values($items) : [$items],
            "repair_amount" => $repairAmount
        ];
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "repair_items" => $this->repairItems
        ]);
    }
}
