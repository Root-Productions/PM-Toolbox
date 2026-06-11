<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\Ammunition;

/** Allows the item to shoot projectile ammunition. */
final class ShooterComponent extends DataDrivenItemComponent {
    /** @param array<int, Ammunition|array{item: string, search_inventory?: bool, use_in_creative?: bool, use_offhand?: bool}> $ammunition */
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
    ): Ammunition {
        return new Ammunition($item, $searchInventory, $useInCreative, $useOffhand);
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
