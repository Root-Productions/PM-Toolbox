<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use InvalidArgumentException;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use valres\toolbox\behavior\item\builder\ItemBuilder;

final class ItemTypeDictionaryMapper {
    use SingletonTrait;

    private ReflectionClass $dictionaryReflection;
    private ReflectionProperty $intToStringProperty;
    private ReflectionProperty $stringToIntProperty;
    private ReflectionProperty $itemTypesProperty;

    private array $items = [];

    /**
     * Caches reflection handles for PocketMine's item type dictionary internals.
     *
     * @internal This class mutates PocketMine network dictionary internals.
     *
     * @throws ReflectionException
     */
    public function __construct() {
        $dictionary = TypeConverter::getInstance()->getItemTypeDictionary();
        $this->dictionaryReflection = new ReflectionClass($dictionary);

        $this->intToStringProperty = $this->dictionaryReflection->getProperty("intToStringIdMap");
        $this->stringToIntProperty = $this->dictionaryReflection->getProperty("stringToIntMap");
        $this->itemTypesProperty = $this->dictionaryReflection->getProperty("itemTypes");
    }

    /**
     * Returns entries registered through the toolbox mapper.
     *
     * @return array<string, array{builder: ItemBuilder, entry: ItemTypeEntry}>
     */
    public function getItems(): array {
        return $this->items;
    }

    /**
     * Adds a custom item entry to PocketMine's runtime item dictionary.
     *
     * @internal Prefer CustomItemRegistry for normal item registration.
     *
     * @param  ItemBuilder   $builder
     * @param  ItemTypeEntry $entry
     *
     * @throws InvalidArgumentException|RuntimeException
     * @return void
     */
    public function map(ItemBuilder $builder, ItemTypeEntry $entry): void {
        $this->validateEntry($entry, false);

        $this->registerEntry($entry, false);

        $this->items[$entry->getStringId()] = [
            "builder" => $builder,
            "entry" => $entry
        ];
    }

    /**
     * Adds a raw item type entry without associating it to a toolbox item builder.
     *
     * @internal Used by block-items that are backed by PocketMine ItemBlock instances.
     */
    public function registerEntry(ItemTypeEntry $entry, bool $allowNegativeNumericId = false): void {
        $this->validateEntry($entry, $allowNegativeNumericId);
        $this->updateMappings(TypeConverter::getInstance()->getItemTypeDictionary(), $entry);
    }

    /**
     * Validates the numeric and string ids before dictionary mutation.
     *
     * @param  ItemTypeEntry $itemTypeEntry
     *
     * @throws InvalidArgumentException
     * @return void
     */
    private static function validateEntry(ItemTypeEntry $itemTypeEntry, bool $allowNegativeNumericId): void {
        $numericId = $itemTypeEntry->getNumericId();
        $stringId = $itemTypeEntry->getStringId();

        if ($numericId < 0 && !$allowNegativeNumericId) {
            throw new InvalidArgumentException("Numeric ID invalid: {$numericId}.");
        }

        if (empty(trim($stringId))) {
            throw new InvalidArgumentException("String ID cannot be empty.");
        }
    }

    /**
     * Mutates PocketMine's private item type maps using cached reflection properties.
     *
     * @internal Reflection bridge for TypeConverter item dictionary internals.
     *
     * @param  object        $dictionary
     * @param  ItemTypeEntry $itemTypeEntry
     *
     * @return void
     * @throws RuntimeException
     */
    private function updateMappings(object $dictionary, ItemTypeEntry $itemTypeEntry): void {
        $numericId = $itemTypeEntry->getNumericId();
        $stringId  = $itemTypeEntry->getStringId();

        $intToStringMap = $this->intToStringProperty->getValue($dictionary);
        $stringToIntMap = $this->stringToIntProperty->getValue($dictionary);

        if (isset($intToStringMap[$numericId])) {
            throw new RuntimeException("Numeric ID '$numericId' already used.");
        }

        if (isset($stringToIntMap[$stringId])) {
            throw new RuntimeException("String ID '$stringId' already used.");
        }

        $intToStringMap[$numericId] = $stringId;
        $stringToIntMap[$stringId]  = $numericId;

        $this->intToStringProperty->setValue($dictionary, $intToStringMap);
        $this->stringToIntProperty->setValue($dictionary, $stringToIntMap);

        $itemTypes = $this->itemTypesProperty->getValue($dictionary);
        $itemTypes[] = $itemTypeEntry;
        $this->itemTypesProperty->setValue($dictionary, $itemTypes);
    }
}
