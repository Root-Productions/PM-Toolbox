<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\DataStore;
use pocketmine\network\mcpe\protocol\types\DataStoreType;

final class DduiDataStoreChange extends DataStore {
    public function __construct(
        private readonly string $name,
        private readonly string $property,
        private readonly int $updateCount,
        private readonly DduiDataStorePropertyValue $value
    ) {
    }

    public function getTypeId(): int {
        return DataStoreType::CHANGE;
    }

    public function write($out): void {
        CommonTypes::putString($out, $this->name);
        CommonTypes::putString($out, $this->property);
        LE::writeUnsignedInt($out, $this->updateCount);
        $this->value->writeWithType($out);
    }
}
