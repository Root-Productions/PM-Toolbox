<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pmmp\encoding\LE;

final class DduiInt64Value extends DduiDataStorePropertyValue {
    public function __construct(private readonly int $value) {
    }

    public function getValue(): int {
        return $this->value;
    }

    public function getTypeId(): int {
        return DduiDataStorePropertyType::INT64;
    }

    protected function writePayload($out): void {
        LE::writeSignedLong($out, $this->value);
    }
}
