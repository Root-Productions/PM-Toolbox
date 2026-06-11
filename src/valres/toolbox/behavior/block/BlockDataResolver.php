<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use pocketmine\block\Flowable;
use valres\toolbox\behavior\block\component\CollisionBoxComponent;
use valres\toolbox\behavior\block\component\ConnectionRuleComponent;
use valres\toolbox\behavior\block\component\DestructibleByExplosionComponent;
use valres\toolbox\behavior\block\component\DestructibleByMiningComponent;
use valres\toolbox\behavior\block\component\DisplayNameBlockComponent;
use valres\toolbox\behavior\block\component\FrictionBlockComponent;
use valres\toolbox\behavior\block\component\LightEmissionComponent;
use valres\toolbox\behavior\block\component\MaterialInstancesComponent;
use valres\toolbox\behavior\block\component\SelectionBoxComponent;
use valres\toolbox\behavior\block\component\type\MaterialInstance;
use valres\toolbox\behavior\block\component\type\OnPlayerPlacingComponent;
use valres\toolbox\behavior\block\component\type\RenderMethod;

class BlockDataResolver {
    public static function applyDefault(BlockBuilder $builder): void {
        $block = $builder->getBlock();

        $builder->addComponent(new MaterialInstancesComponent([
            new MaterialInstance(
                $builder->getName(),
                $block->isTransparent() ? RenderMethod::ALPHA_TEST : RenderMethod::OPAQUE
            )
        ]));
        $builder->addComponent(new DisplayNameBlockComponent("tile." . $builder->getRuntimeId() . ".name"));
        $builder->addComponent(new OnPlayerPlacingComponent());

        $builder->addComponent(new CollisionBoxComponent(!empty($block->getCollisionBoxes())));
        $builder->addComponent(new SelectionBoxComponent());

        $builder->addComponent(new DestructibleByExplosionComponent($block->getBreakInfo()->getBlastResistance()));
        $builder->addComponent(new DestructibleByMiningComponent($block->getBreakInfo()->getHardness() * 3.33334));
        $builder->addComponent(new FrictionBlockComponent(max(0, 1 - $block->getFrictionFactor())));

        if (($lightLevel = $block->getLightLevel()) > 0) {
            $builder->addComponent(new LightEmissionComponent($lightLevel));
        }

        if ($block instanceof Flowable) {
            $builder->addComponent(new ConnectionRuleComponent());
        }

        if ($block instanceof ExtraBlockComponentsInterface) {
            $block->defineBlockComponents($builder);
        }
    }
}
