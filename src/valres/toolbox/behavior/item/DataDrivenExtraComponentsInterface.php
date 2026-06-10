<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;

interface DataDrivenExtraComponentsInterface {
    /**
     * Adds custom data-driven components or properties after toolbox defaults are resolved.
     *
     * @param  DataDrivenItemBuilder $builder
     *
     * @return void
     */
    public function defineDataDrivenComponents(DataDrivenItemBuilder $builder): void;
}
