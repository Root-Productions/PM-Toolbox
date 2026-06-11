<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\block\Block;
use pocketmine\nbt\tag\Tag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use valres\toolbox\behavior\item\component\type\BlockDescriptor;

/** Allows the item to place a block when used. */
final class BlockPlacerItemComponent extends DataDrivenItemComponent {
    /** @param array<int, BlockDescriptor|string|array>|null $useOn */
    public function __construct(
        private readonly string $block,
        private readonly ?bool $replaceBlockItem = null,
        private readonly ?bool $alignedPlacement = null,
        private readonly ?array $useOn = null
    ) {
    }

    public static function from(Block $block, Block ...$useOn): self {
        return new self(
            GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName(),
            useOn: array_map(
                static fn(Block $target): string => GlobalBlockStateHandlers::getSerializer()->serialize($target->getStateId())->getName(),
                $useOn
            )
        );
    }

    public static function identifier(): string {
        return "minecraft:block_placer";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "block" => $this->block,
            "replace_block_item" => $this->replaceBlockItem,
            "aligned_placement" => $this->alignedPlacement,
            "use_on" => $this->useOn
        ]);
    }
}
