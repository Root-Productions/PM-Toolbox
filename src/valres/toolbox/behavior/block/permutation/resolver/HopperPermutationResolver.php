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
use valres\toolbox\behavior\block\component\type\MaterialInstance;
use valres\toolbox\behavior\block\component\type\MaterialInstanceTarget;
use valres\toolbox\behavior\block\component\type\RenderMethod;
use valres\toolbox\behavior\block\PermutationsResolver;
use valres\toolbox\behavior\block\property\BlockStateProperty;

final class HopperPermutationResolver extends PermutationsResolver {
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
            ->add("north", "q.block_state('" . BlockStateNames::FACING_DIRECTION . "') == 2")
            ->add("south", "q.block_state('" . BlockStateNames::FACING_DIRECTION . "') == 3")
            ->add("east", "q.block_state('" . BlockStateNames::FACING_DIRECTION . "') == 5")
            ->add("west", "q.block_state('" . BlockStateNames::FACING_DIRECTION . "') == 4")
            ->add("ground", "q.block_state('" . BlockStateNames::FACING_DIRECTION . "') == 0")
        );

        $builder->addComponent(new MaterialInstancesComponent([
            MaterialInstanceTarget::NORTH->value => new MaterialInstance("{$name}_outside", RenderMethod::ALPHA_TEST),
            MaterialInstanceTarget::EAST->value => new MaterialInstance("{$name}_outside", RenderMethod::ALPHA_TEST),
            MaterialInstanceTarget::SOUTH->value => new MaterialInstance("{$name}_outside", RenderMethod::ALPHA_TEST),
            MaterialInstanceTarget::WEST->value => new MaterialInstance("{$name}_outside", RenderMethod::ALPHA_TEST),
            MaterialInstanceTarget::UP->value => new MaterialInstance("{$name}_top", RenderMethod::ALPHA_TEST),
            MaterialInstanceTarget::DOWN->value => new MaterialInstance("{$name}_inside", RenderMethod::ALPHA_TEST)
        ]));

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
