<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

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
        $NBT = CompoundTag::create()->setTag("block", new StringTag($this->block));

        if (isset($this->replaceBlockItem)) {
            $NBT->setTag("replace_block_item", new ByteTag($this->replaceBlockItem ? 1 : 0));
        }

        if (isset($this->alignedPlacement)) {
            $NBT->setTag("aligned_placement", new ByteTag($this->replaceBlockItem ? 1 : 0));
        }

        if (isset($this->useOn)) {
            $NBT->setTag("use_on", new ListTag($this->useOn, NBT::TAG_String));
        }

        return $NBT;
    }
}