<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\BlockFace;
use valres\toolbox\behavior\block\component\type\LiquidTouchReaction;

final class LiquidDetectionComponent extends BlockComponent {
    /** @param array<int, array<string, mixed>> $detectionRules */
    public function __construct(private readonly array $detectionRules) {
    }

    public static function identifier(): string {
        return "minecraft:liquid_detection";
    }

    /** @param BlockFace[]|string[] $stopsLiquidFlowingFromDirection */
    public static function waterRule(
        bool $canContainLiquid,
        LiquidTouchReaction|string|null $onLiquidTouches = null,
        array $stopsLiquidFlowingFromDirection = [],
        ?bool $useLiquidClipping = null
    ): array {
        return [
            "liquid_type" => "water",
            "can_contain_liquid" => $canContainLiquid,
            "on_liquid_touches" => $onLiquidTouches instanceof LiquidTouchReaction ? $onLiquidTouches->value : $onLiquidTouches,
            "stops_liquid_flowing_from_direction" => $stopsLiquidFlowingFromDirection === [] ? null : array_map(
                static fn(BlockFace|string $face): string => $face instanceof BlockFace ? $face->value : $face,
                $stopsLiquidFlowingFromDirection
            ),
            "use_liquid_clipping" => $useLiquidClipping
        ];
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "detection_rules" => $this->detectionRules
        ]);
    }
}
