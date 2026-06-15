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

    /**
     * Builds the NBT payload used by the Bedrock item type dictionary.
     *
     * @return CompoundTag
     */
    abstract public function toNBT(): CompoundTag;

    /**
     * Creates a builder around an already constructed PocketMine item instance.
     *
     * @param  Item $item
     *
     * @return static
     */
    public static function create(Item $item): self {
        return new static($item);
    }

    public function getItem(): Item {
        return $this->item;
    }

    /**
     * Replaces the item instance used by serializers and auto component resolvers.
     *
     * @param  Item $item
     *
     * @return void
     */
    public function setItem(Item $item): void {
        $this->item = $item;
    }

    public function getRuntimeId(): string {
        return $this->runtimeId;
    }

    /**
     * Sets the namespaced runtime identifier registered for Bedrock clients.
     *
     * @param  string $runtimeId
     *
     * @return $this
     */
    public function setRuntimeId(string $runtimeId): self {
        $this->runtimeId = $runtimeId;
        return $this;
    }

    public function getTypeId(): int {
        return $this->typeId;
    }

    /**
     * Sets the numeric type id used by PocketMine for this custom item.
     *
     * @param  int $typeId
     *
     * @return $this
     */
    public function setTypeId(int $typeId): self {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * Returns the custom deserializer callback when one has been configured.
     *
     * @return Closure|null
     */
    public function getDeserializer(): ?Closure {
        return $this->deserializer;
    }

    /**
     * Returns the custom serializer callback when one has been configured.
     *
     * @return Closure|null
     */
    public function getSerializer(): ?Closure {
        return $this->serializer;
    }
}
