<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use valres\toolbox\behavior\block\builder\BlockBuilder;

interface ExtraBlockComponentsInterface {
    /**
     * Adds custom block components, traits, properties or permutations after toolbox defaults are resolved.
     *
     * @param  BlockBuilder $builder
     *
     * @return void
     */
    public function defineBlockComponents(BlockBuilder $builder): void;
}
