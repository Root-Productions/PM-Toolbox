<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\builder;

use pocketmine\data\bedrock\ItemTagToIdMap;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use valres\toolbox\behavior\item\component\DataDrivenItemComponent;
use valres\toolbox\behavior\item\component\TagsComponent;
use valres\toolbox\behavior\item\ItemFormatEnum;
use valres\toolbox\behavior\item\property\DataDrivenItemProperty;

class DataDrivenItemBuilder extends ItemBuilder {
    /** @var array<string, DataDrivenItemComponent> */
    private array $components = [];

    /** @var array<string, DataDrivenItemProperty> */
    private array $properties = [];

    public static function getFormat(): ItemFormatEnum {
        return ItemFormatEnum::DATA_DRIVEN;
    }

    /**
     * Returns every data-driven component currently attached to the builder.
     *
     * @return array<string, DataDrivenItemComponent>
     */
    public function getComponents(): array {
        return $this->components;
    }

    /**
     * Adds or replaces a data-driven component by its Bedrock identifier.
     *
     * @param  DataDrivenItemComponent $component
     *
     * @return $this
     */
    public function addComponent(DataDrivenItemComponent $component): self {
        $this->components[$component::identifier()] = $component;
        return $this;
    }

    /**
     * Removes a data-driven component by its Bedrock identifier.
     *
     * @param  string $componentId
     *
     * @return $this
     */
    public function removeComponent(string $componentId): self {
        unset($this->components[$componentId]);
        return $this;
    }

    /**
     * Checks whether a data-driven component is present on the builder.
     *
     * @param  string $componentId
     *
     * @return bool
     */
    public function hasComponent(string $componentId): bool {
        return isset($this->components[$componentId]);
    }

    /**
     * Returns every item property currently attached to the builder.
     *
     * @return array<string, DataDrivenItemProperty>
     */
    public function getProperties(): array {
        return $this->properties;
    }

    /**
     * Adds or replaces an item property by its Bedrock property identifier.
     *
     * @param  DataDrivenItemProperty $property
     *
     * @return $this
     */
    public function addProperty(DataDrivenItemProperty $property): self {
        $this->properties[$property::identifier()] = $property;
        return $this;
    }

    /**
     * Removes an item property by its Bedrock property identifier.
     *
     * @param  string $propertyId
     *
     * @return $this
     */
    public function removeProperty(string $propertyId): self {
        unset($this->properties[$propertyId]);
        return $this;
    }

    /**
     * Checks whether an item property is present on the builder.
     *
     * @param  string $propertyId
     *
     * @return bool
     */
    public function hasProperty(string $propertyId): bool {
        return isset($this->properties[$propertyId]);
    }

    /**
     * Builds the network component NBT sent through the item type dictionary.
     *
     * @return CompoundTag
     */
    public function toNBT(): CompoundTag {
        $components = CompoundTag::create();
        $properties = CompoundTag::create();

        foreach ($this->getProperties() as $id => $property) {
            $properties->setTag($id, $property->toNBT());
        }

        foreach ($this->getComponents() as $id => $component) {
            $components->setTag($id, $component->toNBT());
            $this->registerTags($component);
        }

        $components->setTag(static::TAG_ITEM_PROPERTIES, $properties);
        return CompoundTag::create()
            ->setTag(static::TAG_ID, new IntTag($this->getTypeId()))
            ->setTag(static::TAG_NAME, new StringTag($this->getRuntimeId()))
            ->setTag(static::TAG_COMPONENTS, $components);
    }

    /**
     * Mirrors minecraft:tags into PocketMine's internal item tag map.
     *
     * @internal PocketMine marks ItemTagToIdMap as internal, so this method uses reflection.
     *
     * @param  DataDrivenItemComponent $component
     *
     * @return void
     */
    private function registerTags(DataDrivenItemComponent $component): void {
        if (!$component instanceof TagsComponent) {
            return;
        }

        $typeName = GlobalItemDataHandlers::getSerializer()
            ->serializeType($this->getItem())
            ->getName();

        foreach ($component->getTags() as $tag) {
            ItemTagToIdMap::getInstance()->addIdToTag($tag, $typeName);
        }
    }
}
