<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final class DduiBoolValue extends DduiDataStorePropertyValue {
    public function __construct(private readonly bool $value) {
    }

    public function getValue(): bool {
        return $this->value;
    }

    public function getTypeId(): int {
        return DduiDataStorePropertyType::BOOL;
    }

    protected function writePayload($out): void {
        CommonTypes::putBool($out, $this->value);
    }
}
