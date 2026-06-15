<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\permutation\resolver;

use pocketmine\block\Hopper;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use valres\toolbox\behavior\block\BlockBuilder;
use valres\toolbox\behavior\block\component\GeometryComponent;
use valres\toolbox\behavior\block\component\MaterialInstancesComponent;
use valres\toolbox\behavior\block\component\type\RenderMethod;
use valres\toolbox\behavior\block\permutation\attribute\BlockPermutationResolver;
use valres\toolbox\behavior\block\permutation\BlockStateQuery;
use valres\toolbox\behavior\block\property\BlockStateProperty;

#[BlockPermutationResolver]
final class HopperPermutationResolver extends VanillaPermutationResolver {
    public function supports(BlockBuilder $builder): bool {
        return $builder->getBlock() instanceof Hopper;
    }

    public function resolve(BlockBuilder $builder): void {
        $block = $builder->getBlock();
        if (!$block instanceof Hopper) {
            return;
        }

        $runtimeId = $builder->getRuntimeId();
        $name = $builder->getName();

        $builder->addProperty(new BlockStateProperty(BlockStateNames::FACING_DIRECTION, range(0, 5)));
        $builder->addProperty(new BlockStateProperty(BlockStateNames::TOGGLE_BIT, range(0, 1)));

        $builder->addComponent((new GeometryComponent("minecraft.hopper"))
            ->add("north", BlockStateQuery::equals(BlockStateNames::FACING_DIRECTION, 2))
            ->add("south", BlockStateQuery::equals(BlockStateNames::FACING_DIRECTION, 3))
            ->add("east", BlockStateQuery::equals(BlockStateNames::FACING_DIRECTION, 5))
            ->add("west", BlockStateQuery::equals(BlockStateNames::FACING_DIRECTION, 4))
            ->add("ground", BlockStateQuery::equals(BlockStateNames::FACING_DIRECTION, 0))
        );

        $builder->addComponent(MaterialInstancesComponent::sided(
            "{$name}_outside",
            "{$name}_top",
            "{$name}_inside",
            RenderMethod::ALPHA_TEST
        ));

        $builder->setSerializer(static fn(Hopper $block): BlockStateWriter => (new BlockStateWriter($runtimeId))
            ->writeInt(BlockStateNames::TOGGLE_BIT, $block->isPowered() ? 1 : 0)
            ->writeFacingWithoutUp($block->getFacing())
        );
        $builder->setDeserializer(static fn(BlockStateReader $in): Hopper => (clone $block)
            ->setPowered($in->readInt(BlockStateNames::TOGGLE_BIT) === 1)
            ->setFacing($in->readFacingWithoutUp())
        );
    }
}
