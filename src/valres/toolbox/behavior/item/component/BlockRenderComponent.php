<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\block\Block;
use pocketmine\nbt\tag\Tag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

/** Defines the block render identifier used by legacy block items. */
final class BlockRenderComponent extends LegacyItemComponent {
    public function __construct(private readonly string $name) {
    }

    public static function from(Block $block): self {
        return new self(GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName());
    }

    public static function identifier(): string {
        return "minecraft:block";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->name);
    }
}
