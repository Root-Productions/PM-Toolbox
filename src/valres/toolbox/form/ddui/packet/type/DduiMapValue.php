<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pmmp\encoding\VarInt;

final class DduiMapValue extends DduiDataStorePropertyValue {
    /** @param DduiMapEntry[] $entries */
    public function __construct(private readonly array $entries) {
    }

    public function getEntries(): array {
        return $this->entries;
    }

    public function getTypeId(): int {
        return DduiDataStorePropertyType::MAP;
    }

    protected function writePayload($out): void {
        VarInt::writeUnsignedInt($out, count($this->entries));
        foreach ($this->entries as $entry) {
            $entry->write($out);
        }
    }
}
