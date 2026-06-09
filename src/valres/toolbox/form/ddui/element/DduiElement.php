<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\element;

use valres\toolbox\form\ddui\DduiRenderContext;
use valres\toolbox\form\ddui\packet\type\DduiMapEntry;

interface DduiElement {
    /** @return DduiMapEntry[] */
    public function build(DduiRenderContext $context, int $index): array;
}
