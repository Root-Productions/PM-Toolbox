<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

final class DduiNoneValue extends DduiDataStorePropertyValue {
    public function getTypeId(): int {
        return DduiDataStorePropertyType::NONE;
    }

    protected function writePayload($out): void {
    }
}
