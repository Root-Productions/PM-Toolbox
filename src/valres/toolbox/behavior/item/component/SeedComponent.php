<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use valres\toolbox\behavior\block\component\type\BlockFace;

final class SeedComponent extends LegacyItemComponent {
    /** @param string[] $plantAt */
    public function __construct(
        private readonly string $cropResult,
        private readonly array $plantAt = [],
        private readonly BlockFace $plantAtFace = BlockFace::UP,
    ) {
    }

    public static function fromBlocks(Block $cropResult, Block ...$plantAt): self {
        return new self(
            self::blockName($cropResult),
            array_map(static fn(Block $block): string => self::blockName($block), $plantAt)
        );
    }

    public static function identifier(): string {
        return "minecraft:seed";
    }

    public function toNBT(): CompoundTag {
        return ComponentNbtHelper::compound([
            "crop_result" => $this->normalizeBlockName($this->cropResult),
            "plant_at_face" => $this->plantAtFace,
            "plant_at_any_solid_surface" => $this->plantAt === [],
            "plant_at" => array_map(
                fn(string $block): string => $this->normalizeBlockName($block),
                $this->plantAt
            )
        ]);
    }

    private static function blockName(Block $block): string {
        return GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName();
    }

    private function normalizeBlockName(string $blockName): string {
        return str_replace(
            ["minecraft:", "grass_block", "air"],
            ["", "grass", "light_block"],
            strtolower($blockName)
        );
    }
}
