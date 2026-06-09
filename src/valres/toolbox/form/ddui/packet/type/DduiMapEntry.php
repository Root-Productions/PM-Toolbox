<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final class DduiMapEntry {
    public function __construct(
        private readonly string $key,
        private readonly DduiDataStorePropertyValue $value
    ) {
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getValue(): DduiDataStorePropertyValue {
        return $this->value;
    }

    public function write($out): void {
        CommonTypes::putString($out, $this->key);
        $this->value->writeWithType($out);
    }
}
