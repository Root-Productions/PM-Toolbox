<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

interface ItemComponentValue {
    /** @return array<string, mixed> */
    public function toArray(): array;
}
