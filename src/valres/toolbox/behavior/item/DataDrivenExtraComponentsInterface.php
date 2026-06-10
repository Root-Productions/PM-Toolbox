<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;

interface DataDrivenExtraComponentsInterface {
    public function defineDataDrivenComponents(DataDrivenItemBuilder $builder): void;
}
