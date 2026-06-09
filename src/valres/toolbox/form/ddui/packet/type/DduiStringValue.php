<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final class DduiStringValue extends DduiDataStorePropertyValue {
    public function __construct(private readonly string $value) {
    }

    public function getValue(): string {
        return $this->value;
    }

    public function getTypeId(): int {
        return DduiDataStorePropertyType::STRING;
    }

    protected function writePayload($out): void {
        CommonTypes::putString($out, $this->value);
    }
}
