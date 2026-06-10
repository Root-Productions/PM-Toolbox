<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use valres\toolbox\behavior\item\builder\LegacyItemBuilder;

interface LegacyExtraComponentsInterface {
    /**
     * Adds custom legacy components after toolbox defaults are resolved.
     *
     * @param  LegacyItemBuilder $builder
     *
     * @return void
     */
    public function defineLegacyComponents(LegacyItemBuilder $builder): void;
}
