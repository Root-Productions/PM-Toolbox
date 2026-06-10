<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\builder;

use Closure;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use valres\toolbox\behavior\item\ItemFormatEnum;

abstract class ItemBuilder {
    const TAG_ID = "id";
    const TAG_NAME = "name";

    const TAG_COMPONENTS = "components";
    const TAG_ITEM_PROPERTIES = "item_properties";

    private Item $item;

    private string $runtimeId;
    private int $typeId;

    private ?Closure $serializer = null;
    private ?Closure $deserializer = null;

    public function __construct(Item $item) {
        $this->item = $item;
    }

    abstract public static function getFormat(): ItemFormatEnum;
    abstract public function toNBT(): CompoundTag;

    public static function create(Item $item): self {
        return new static($item);
    }

    public function getItem(): Item {
        return $this->item;
    }

    public function setItem(Item $item): void {
        $this->item = $item;
    }

    public function getRuntimeId(): string {
        return $this->runtimeId;
    }

    public function setRuntimeId(string $runtimeId): self {
        $this->runtimeId = $runtimeId;
        return $this;
    }

    public function getTypeId(): int {
        return $this->typeId;
    }

    public function setTypeId(int $typeId): self {
        $this->typeId = $typeId;
        return $this;
    }

    public function getDeserializer(): ?Closure {
        return $this->deserializer;
    }

    public function getSerializer(): ?Closure {
        return $this->serializer;
    }
}