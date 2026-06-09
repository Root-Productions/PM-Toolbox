<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pmmp\encoding\VarInt;

final class DduiListValue extends DduiDataStorePropertyValue {
    /** @param DduiDataStorePropertyValue[] $entries */
    public function __construct(private readonly array $entries) {
    }

    public function getTypeId(): int {
        return DduiDataStorePropertyType::LIST;
    }

    protected function writePayload($out): void {
        VarInt::writeUnsignedInt($out, count($this->entries));
        foreach ($this->entries as $entry) {
            $entry->writeWithType($out);
        }
    }
}
