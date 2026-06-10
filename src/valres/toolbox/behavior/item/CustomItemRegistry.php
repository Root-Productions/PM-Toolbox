<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use Closure;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionException;
use valres\toolbox\behavior\exception\ItemRegistryException;
use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;
use valres\toolbox\behavior\item\builder\ItemBuilder;
use valres\toolbox\behavior\item\builder\LegacyItemBuilder;

final class CustomItemRegistry {
    use SingletonTrait;

    /** @var array<string, ItemBuilder> */
    private array $items = [];

    /** @throws ItemRegistryException|ReflectionException */
    public function register(string $runtimeId, Closure $itemClosure): void {
        $item = $itemClosure();
        if (!$item instanceof Item) {
            throw new ItemRegistryException("");
        }

        if (isset($this->items[$runtimeId])) {
            throw new ItemRegistryException("Item '". $runtimeId . "' is already registered.");
        }

        $format = ItemFormatEnum::fromItem($item);
        if ($format === ItemFormatEnum::LEGACY) {
            $this->registerLegacyItem($runtimeId, $item);
        } else {
            $this->registerDataDrivenItem($runtimeId, $item);
        }
    }

    /** @throws ReflectionException|ItemRegistryException */
    public function registerLegacyItem(string $runtimeId, Item $item): void {
        $format = ItemFormatEnum::fromItem($item);
        if ($format !== ItemFormatEnum::LEGACY) {
            throw new ItemRegistryException("Item must be a Legacy item.");
        }

        $itemBuilder = LegacyItemBuilder::create($item)
            ->setRuntimeId($runtimeId)
            ->setTypeId($item->getTypeId());

        $this->applyItemComponents($itemBuilder);
        $this->deepRegister($itemBuilder);
    }

    /** @throws ReflectionException|ItemRegistryException */
    public function registerDataDrivenItem(string $runtimeId, Item $item): void {
        $format = ItemFormatEnum::fromItem($item);
        if ($format !== ItemFormatEnum::DATA_DRIVEN) {
            throw new ItemRegistryException("Item must be a Data-Driven item.");
        }

        $itemBuilder = DataDrivenItemBuilder::create($item)
            ->setRuntimeId($runtimeId)
            ->setTypeId($item->getTypeId());

        $this->applyItemComponents($itemBuilder);
        $this->deepRegister($itemBuilder);
    }

    /** @throws ItemRegistryException */
    private function applyItemComponents(ItemBuilder $builder): void {
        $item = $builder->getItem();

        ItemDataResolver::applyDefault($builder);

        if ($builder instanceof DataDrivenItemBuilder) {
            if ($item instanceof LegacyExtraComponentsInterface) {
                throw new ItemRegistryException("Data-Driven item '" . $item::class . "' cannot define legacy components.");
            }

            if ($item instanceof DataDrivenExtraComponentsInterface) {
                $item->defineDataDrivenComponents($builder);
            }

            return;
        }

        if ($builder instanceof LegacyItemBuilder) {
            if ($item instanceof DataDrivenExtraComponentsInterface) {
                throw new ItemRegistryException("Legacy item '" . $item::class . "' cannot define Data-Driven components.");
            }

            if ($item instanceof LegacyExtraComponentsInterface) {
                $item->defineLegacyComponents($builder);
            }
        }
    }

    public function deepRegister(ItemBuilder $builder): void {
        $item = $builder->getItem();
        $runtimeId = $builder->getRuntimeId();
        $typeId = $builder->getTypeId();

        GlobalItemDataHandlers::getDeserializer()
            ->map($runtimeId, $builder->getDeserializer() ?? fn() => clone $item);
        GlobalItemDataHandlers::getSerializer()
            ->map($item, $builder->getSerializer() ?? fn() => new SavedItemData($runtimeId));

        ItemTypeDictionaryMapper::getInstance()->map($builder, new ItemTypeEntry(
            $runtimeId,
            $typeId,
            ($builder::getFormat() === ItemFormatEnum::DATA_DRIVEN),
            $builder::getFormat()->value,
            new CacheableNbt($builder->toNBT())
        ));

        $identifier = $builder->getRuntimeId();
        $legacyIdentifier = $identifier;
        if (str_contains($identifier, ":")) {
            [, $legacyIdentifier] = explode(":", $identifier, 2);
            StringToItemParser::getInstance()->register($legacyIdentifier, fn () => clone $item);
        }
        StringToItemParser::getInstance()->register($identifier, fn () => clone $item);
        LegacyItemIdToStringIdMap::getInstance()->add($legacyIdentifier, $item->getTypeId());

        $this->items[$runtimeId] = $builder;
    }
}
