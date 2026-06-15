<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\permutation\resolver;

use pocketmine\block\Crops;
use pocketmine\block\NetherWartPlant;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use valres\toolbox\behavior\block\BlockBuilder;
use valres\toolbox\behavior\block\component\CollisionBoxComponent;
use valres\toolbox\behavior\block\component\GeometryComponent;
use valres\toolbox\behavior\block\component\MaterialInstancesComponent;
use valres\toolbox\behavior\block\component\SelectionBoxComponent;
use valres\toolbox\behavior\block\component\type\MaterialInstance;
use valres\toolbox\behavior\block\component\type\RenderMethod;
use valres\toolbox\behavior\block\permutation\BlockPermutation;
use valres\toolbox\behavior\block\PermutationsResolver;
use valres\toolbox\behavior\block\property\BlockStateProperty;

final class CropsPermutationResolver extends PermutationsResolver {
    public function resolve(BlockBuilder $builder): void {
        $block = $builder->getBlock();
        if (!$block instanceof Crops && !$block instanceof NetherWartPlant) {
            return;
        }

        $runtimeId = $builder->getRuntimeId();
        $stateName = BlockStateNames::GROWTH;
        $ages = range(0, $block::MAX_AGE);

        $builder->addProperty(new BlockStateProperty($stateName, $ages));
        $builder->addComponent(new GeometryComponent("geometry.crop"));
        $builder->addComponent(new CollisionBoxComponent(false));

        foreach ($ages as $age) {
            $height = (($age + 1.0) * (1 / $block::MAX_AGE)) * 0.7 * 16;
            $permutation = new BlockPermutation("q.block_state('{$stateName}') == {$age}");
            $permutation->addComponent(MaterialInstancesComponent::all(
                new MaterialInstance($builder->getName() . "_{$age}", RenderMethod::ALPHA_TEST)
            ));
            $permutation->addComponent(SelectionBoxComponent::box([-8.0, 0.0, -8.0], [16.0, $height, 16.0]));
            $builder->addPermutation($permutation);
        }

        $builder->setDeserializer(static fn(BlockStateReader $in): Crops|NetherWartPlant => (clone $block)
            ->setAge($in->readBoundedInt(BlockStateNames::GROWTH, 0, $block::MAX_AGE))
        );
        $builder->setSerializer(static fn(Crops|NetherWartPlant $block): BlockStateWriter => (new BlockStateWriter($runtimeId))
            ->writeInt(BlockStateNames::GROWTH, $block->getAge())
        );
    }
}
