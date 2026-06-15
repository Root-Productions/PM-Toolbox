<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\permutation;

final class BlockStateQuery {
    public static function equals(string $stateName, bool|int|float|string $value): string {
        return self::state($stateName) . " == " . self::value($value);
    }

    public static function notEquals(string $stateName, bool|int|float|string $value): string {
        return self::state($stateName) . " != " . self::value($value);
    }

    public static function state(string $stateName): string {
        return "q.block_state('" . str_replace("'", "\\'", $stateName) . "')";
    }

    private static function value(bool|int|float|string $value): string {
        return match (true) {
            is_bool($value) => $value ? "true" : "false",
            is_string($value) => "'" . str_replace("'", "\\'", $value) . "'",
            default => (string) $value
        };
    }
}
