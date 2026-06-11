<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\BlockDescriptor;
use valres\toolbox\behavior\item\component\type\RepairItem;

/** Defines items and amounts that can repair this item. */
final class RepairableComponent extends DataDrivenItemComponent {
    /** @param array<int, RepairItem|array{items: list<string|array>, repair_amount: string|int}> $repairItems */
    public function __construct(private readonly array $repairItems) {
    }

    public static function identifier(): string {
        return "minecraft:repairable";
    }

    public static function repairItem(BlockDescriptor|array|string $items, string|int $repairAmount): RepairItem {
        return RepairItem::of($items, $repairAmount);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "repair_items" => $this->repairItems
        ]);
    }
}
