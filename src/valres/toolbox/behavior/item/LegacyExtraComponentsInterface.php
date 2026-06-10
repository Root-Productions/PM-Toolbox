<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use valres\toolbox\behavior\item\builder\LegacyItemBuilder;

interface LegacyExtraComponentsInterface {
    public function defineLegacyComponents(LegacyItemBuilder $builder): void;
}
