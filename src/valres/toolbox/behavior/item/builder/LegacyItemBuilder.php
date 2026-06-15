<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\builder;

use pocketmine\nbt\tag\CompoundTag;
use valres\toolbox\behavior\item\component\LegacyItemComponent;
use valres\toolbox\behavior\item\ItemFormatEnum;

class LegacyItemBuilder extends ItemBuilder {
    /** @var array<string, LegacyItemComponent> */
    private array $components = [];

    public static function getFormat(): ItemFormatEnum {
        return ItemFormatEnum::LEGACY;
    }

    /**
     * Returns every legacy component currently attached to the builder.
     *
     * @return array<string, LegacyItemComponent>
     */
    public function getComponents(): array {
        return $this->components;
    }

    /**
     * Adds or replaces a legacy component by its Bedrock identifier.
     *
     * @param  LegacyItemComponent $component
     *
     * @return $this
     */
    public function addComponent(LegacyItemComponent $component): self {
        $this->components[$component::identifier()] = $component;
        return $this;
    }

    /**
     * Removes a legacy component by its Bedrock identifier.
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
     * Checks whether a legacy component is present on the builder.
     *
     * @param  string $componentId
     *
     * @return bool
     */
    public function hasComponent(string $componentId): bool {
        return isset($this->components[$componentId]);
    }

    /**
     * Builds the legacy component NBT sent through the item type dictionary.
     *
     * @return CompoundTag
     */
    public function toNBT(): CompoundTag {
        $components = CompoundTag::create();

        foreach ($this->components as $id => $component) {
            $components->setTag($id, $component->toNBT());
        }

        return CompoundTag::create()->setTag(static::TAG_COMPONENTS, $components);
    }
}
