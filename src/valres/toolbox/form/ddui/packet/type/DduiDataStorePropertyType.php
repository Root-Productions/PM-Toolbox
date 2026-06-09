<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

final class DduiDataStorePropertyType {
    public const NONE = 0;
    public const BOOL = 1;
    public const INT64 = 2;
    public const STRING = 4;
    public const LIST = 5;
    public const MAP = 6;

    private function __construct() {
    }
}
