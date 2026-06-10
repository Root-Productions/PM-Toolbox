<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Allows the item to place a block when used. */
final class BlockPlacerItemComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly string $block,
        private readonly ?bool $replaceBlockItem = null,
        private readonly ?bool $alignedPlacement = null,
        private readonly ?array $useOn = null
    ) {
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
