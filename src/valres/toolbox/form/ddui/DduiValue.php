<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use pocketmine\network\mcpe\protocol\types\cereal\DynamicValue;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueBool;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueDouble;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueLong;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueMap;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueString;

final class DduiValue {
    private function __construct() {}

    /**
     * @param array<string, DynamicValue|null> $entries
     */
    public static function map(array $entries): DynamicValueMap {
        return new DynamicValueMap($entries);
    }

    public static function bool(bool $value): DynamicValueBool {
        return new DynamicValueBool($value);
    }

    public static function string(string $value): DynamicValueString {
        return new DynamicValueString($value);
    }

    public static function long(int $value): DynamicValueLong {
        return new DynamicValueLong($value);
    }

    public static function double(float $value): DynamicValueDouble {
        return new DynamicValueDouble($value);
    }
}
