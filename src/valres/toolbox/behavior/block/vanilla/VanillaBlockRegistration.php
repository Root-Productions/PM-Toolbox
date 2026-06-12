<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\vanilla;

use pocketmine\block\Crops;
use valres\toolbox\behavior\block\BlockBuilder;

final class VanillaBlockRegistration {
    public static function apply(BlockBuilder $builder): void {
        if ($builder->getPermutations() !== []) {
            return;
        }

        $block = $builder->getBlock();
        if ($block instanceof Crops) {
            CropBlockRegistration::apply($builder, $block);
        }
    }
}
