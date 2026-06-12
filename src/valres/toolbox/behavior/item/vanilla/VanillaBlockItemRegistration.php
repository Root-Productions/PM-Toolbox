<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\vanilla;

use pocketmine\block\Crops;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemIdentifier;
use valres\toolbox\behavior\block\BlockBuilder;
use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;
use valres\toolbox\behavior\item\builder\ItemBuilder;
use valres\toolbox\behavior\item\builder\LegacyItemBuilder;

final class VanillaBlockItemRegistration {
    public static function createBuilder(BlockBuilder $builder, Item $item): ItemBuilder {
        $block = $builder->getBlock();
        if ($block instanceof Crops) {
            return CropSeedItemRegistration::createBuilder($builder, $block);
        }

        if ($item instanceof ItemBlock) {
            $generated = new DefaultBlockItem(new ItemIdentifier(-$block->getTypeId()), $block->getName(), clone $block);

            return DataDrivenItemBuilder::create($generated)
                ->setRuntimeId($builder->getRuntimeId())
                ->setTypeId($generated->getTypeId());
        }

        return $builder->getItemBuilder();
    }

    public static function applyComponents(ItemBuilder $builder, BlockBuilder $blockBuilder): void {
        $block = $blockBuilder->getBlock();
        if ($builder instanceof LegacyItemBuilder && $block instanceof Crops) {
            CropSeedItemRegistration::applyComponents($builder, $block);
        }
    }
}
