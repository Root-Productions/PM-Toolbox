<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\vanilla;

use pocketmine\block\Crops;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemIdentifier;
use valres\toolbox\behavior\block\BlockBuilder;
use valres\toolbox\behavior\item\builder\LegacyItemBuilder;
use valres\toolbox\behavior\item\component\SeedComponent;

final class CropSeedItemRegistration {
    public static function createBuilder(BlockBuilder $builder, Crops $block): LegacyItemBuilder {
        $item = new CropSeedItem(new ItemIdentifier(-$block->getTypeId()), $block->getName(), clone $block);

        return LegacyItemBuilder::create($item)
            ->setRuntimeId($builder->getRuntimeId())
            ->setTypeId($item->getTypeId());
    }

    public static function applyComponents(LegacyItemBuilder $builder, Crops $block): void {
        if (!$builder->hasComponent(SeedComponent::identifier())) {
            $builder->addComponent(SeedComponent::fromBlocks($block, VanillaBlocks::FARMLAND()));
        }
    }
}
