<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\operation;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValue;
use pocketmine\network\mcpe\protocol\types\cereal\DynamicValueType;
use pocketmine\network\mcpe\protocol\types\ddui\DataStoreOperation;
use pocketmine\network\mcpe\protocol\types\ddui\DataStoreOperationType;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class DataStoreChangeOperation implements DataStoreOperation {
    use GetTypeIdFromConstTrait;

    public const ID = DataStoreOperationType::CHANGE;

    public function __construct(
        private string $name,
        private string $property,
        private int $updateCount,
        private ?DynamicValue $data
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getProperty(): string {
        return $this->property;
    }

    public function getUpdateCount(): int {
        return $this->updateCount;
    }

    public function getData(): ?DynamicValue {
        return $this->data;
    }

    public static function read(ByteBufferReader $in): self {
        $name = CommonTypes::getString($in);
        $property = CommonTypes::getString($in);
        $updateCount = LE::readUnsignedInt($in);
        $type = LE::readUnsignedInt($in);

        return new self(
            $name,
            $property,
            $updateCount,
            DynamicValue::read($in, $type)
        );
    }

    public function write(ByteBufferWriter $out): void {
        CommonTypes::putString($out, $this->name);
        CommonTypes::putString($out, $this->property);
        LE::writeUnsignedInt($out, $this->updateCount);

        $type = $this->data?->getTypeId() ?? DynamicValueType::NULL;
        LE::writeUnsignedInt($out, $type);
        $this->data?->write($out);
    }
}
