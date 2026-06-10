<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\builder;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use valres\toolbox\behavior\item\component\DataDrivenItemComponent;
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

    public function getComponents(): array {
        return $this->components;
    }

    public function addComponent(DataDrivenItemComponent $component): self {
        $this->components[$component::identifier()] = $component;
        return $this;
    }

    public function removeComponent(string $componentId): self {
        unset($this->components[$componentId]);
        return $this;
    }

    public function hasComponent(string $componentId): bool {
        return isset($this->components[$componentId]);
    }

    public function getProperties(): array {
        return $this->properties;
    }

    public function addProperty(DataDrivenItemProperty $property): self {
        $this->properties[$property::identifier()] = $property;
        return $this;
    }

    public function removeProperty(string $propertyId): self {
        unset($this->properties[$propertyId]);
        return $this;
    }

    public function hasProperty(string $propertyId): bool {
        return isset($this->properties[$propertyId]);
    }

    public function toNBT(): CompoundTag {
        $components = CompoundTag::create();
        $properties = CompoundTag::create();

        foreach ($this->getProperties() as $id => $property) {
            $properties->setTag($id, $property->toNBT());
        }

        foreach ($this->getComponents() as $id => $component) {
            $components->setTag($id, $component->toNBT());
        }

        $components->setTag(static::TAG_ITEM_PROPERTIES, $properties);
        return CompoundTag::create()
            ->setTag(static::TAG_ID, new IntTag($this->getTypeId()))
            ->setTag(static::TAG_NAME, new StringTag($this->getRuntimeId()))
            ->setTag(static::TAG_COMPONENTS, $components);
    }
}
