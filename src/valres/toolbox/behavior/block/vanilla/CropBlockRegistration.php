<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\vanilla;

use pocketmine\block\Crops;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use valres\toolbox\behavior\block\BlockBuilder;
use valres\toolbox\behavior\block\component\CollisionBoxComponent;
use valres\toolbox\behavior\block\component\GeometryComponent;
use valres\toolbox\behavior\block\component\MaterialInstancesComponent;
use valres\toolbox\behavior\block\component\SelectionBoxComponent;
use valres\toolbox\behavior\block\component\type\BlockBox;
use valres\toolbox\behavior\block\component\type\MaterialInstance;
use valres\toolbox\behavior\block\component\type\RenderMethod;
use valres\toolbox\behavior\block\permutation\BlockPermutation;
use valres\toolbox\behavior\block\property\BlockStateProperty;

final class CropBlockRegistration {
    public static function apply(BlockBuilder $builder, Crops $block): void {
        $stateName = BlockStateNames::GROWTH;
        $ages = range(0, $block::MAX_AGE);

        if (!$builder->hasProperty($stateName)) {
            $builder->addProperty(new BlockStateProperty($stateName, $ages));
        }

        $builder->addComponent(new GeometryComponent("geometry.crop"));

        foreach ($ages as $age) {
            $height = max(2.0, min(16.0, (($age + 1.0) / ($block::MAX_AGE + 1.0)) * 16.0));
            $builder->addPermutation((new BlockPermutation("q.block_state('{$stateName}') == {$age}"))
                ->addComponent(MaterialInstancesComponent::all(
                    new MaterialInstance($builder->getName() . "_{$age}", RenderMethod::ALPHA_TEST)
                ))
                ->addComponent(new SelectionBoxComponent(
                    new BlockBox([-8.0, 0.0, -8.0], [16.0, $height, 16.0])
                ))
                ->addComponent(new CollisionBoxComponent(false)));
        }

        $runtimeId = $builder->getRuntimeId();
        $builder->setDeserializer(static fn(BlockStateReader $in): Crops => (clone $block)->setAge(
            $in->readBoundedInt($stateName, 0, $block::MAX_AGE)
        ));
        $builder->setSerializer(static fn(Crops $block): BlockStateWriter => (new BlockStateWriter($runtimeId))
            ->writeInt($stateName, $block->getAge()));
    }
}
