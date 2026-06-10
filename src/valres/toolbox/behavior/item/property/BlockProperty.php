<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\block\Block;
use pocketmine\nbt\tag\Tag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the block render identifier used when a Data-Driven item represents a block. */
final class BlockProperty extends DataDrivenItemProperty {
    public function __construct(private readonly string $blockName) {
    }

    public static function from(Block $block): self {
        return new self(GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName());
    }

    public static function identifier(): string {
        return "block";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->blockName);
    }
}
