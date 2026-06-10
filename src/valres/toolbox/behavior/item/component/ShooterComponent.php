<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class ShooterComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly array $ammunition,
        private readonly ?bool $chargeOnDraw = null,
        private readonly ?float $maxDrawDuration = null,
        private readonly ?bool $scalePowerByDrawDuration = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:shooter";
    }

    public static function ammunition(
        string $item,
        ?bool $searchInventory = null,
        ?bool $useInCreative = null,
        ?bool $useOffhand = null
    ): array {
        return array_filter([
            "item" => $item,
            "search_inventory" => $searchInventory,
            "use_in_creative" => $useInCreative,
            "use_offhand" => $useOffhand
        ], static fn(mixed $value): bool => $value !== null);
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "ammunition" => $this->ammunition,
            "charge_on_draw" => $this->chargeOnDraw,
            "max_draw_duration" => $this->maxDrawDuration,
            "scale_power_by_draw_duration" => $this->scalePowerByDrawDuration
        ]);
    }
}
