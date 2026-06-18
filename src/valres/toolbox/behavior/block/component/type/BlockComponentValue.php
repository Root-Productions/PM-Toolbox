<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

use BackedEnum;

/** Provides scalar enum normalization for component value objects. */
trait BlockComponentValue {
    private static function enumValue(BackedEnum|string|null $value): ?string {
        return $value instanceof BackedEnum ? (string) $value->value : $value;
    }
}
