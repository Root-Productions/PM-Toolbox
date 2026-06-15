<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use Closure;
use Generator;
use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use ReflectionException;
use valres\toolbox\behavior\block\CustomBlockRegistry;
use valres\toolbox\behavior\creative\CreativeInventoryManager;
use valres\toolbox\behavior\exception\ItemRegistryException;
use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;
use valres\toolbox\behavior\item\builder\ItemBuilder;
use valres\toolbox\behavior\item\builder\LegacyItemBuilder;
use valres\toolbox\task\TaskHandle;
use valres\toolbox\task\Tasks;

final class CustomItemRegistry {
    use SingletonTrait;

    /** @var array<string, ItemBuilder> */
    private array $items = [];

    /** @var array<int, string> */
    private array $typeIds = [];

    /**
     * Registers a custom item and resolves its format from class attributes.
     *
     * @param  string  $runtimeId
     * @param  Closure $itemClosure
     *
     * @throws ItemRegistryException|ReflectionException
     * @return void
     */
    public function register(string $runtimeId, Closure $itemClosure): void {
        $item = $itemClosure();
        if (!$item instanceof Item) {
            throw new ItemRegistryException("Item closure must return an Item.");
        }

        if (isset($this->items[$runtimeId])) {
            throw new ItemRegistryException("Item '". $runtimeId . "' is already registered.");
        }

        $format = $this->resolveItemFormat($item);
        if ($format === ItemFormatEnum::LEGACY) {
            $this->registerLegacyItem($runtimeId, $item);
        } else {
            $this->registerDataDrivenItem($runtimeId, $item);
        }
    }

    /**
     * Registers every item exposed by a static item registry such as PocketMine's CloningRegistryTrait.
     *
     * The registry class must expose a public static getAll(): array method returning Item instances.
     *
     * @param  class-string $registryClass
     * @param  string       $namespace
     * @param  Closure|null $runtimeIdResolver function(string $name, Item $item, string $namespace): string
     *
     * @throws ItemRegistryException|ReflectionException
     * @return int
     */
    public static function registerAll(
        string $registryClass,
        string $namespace,
        ?Closure $runtimeIdResolver = null
    ): int {
        return self::getInstance()->registerAllFrom($registryClass, $namespace, $runtimeIdResolver);
    }

    /**
     * Schedules registry loading over multiple ticks to reduce startup stalls.
     *
     * @param  class-string $registryClass
     * @param  string       $namespace
     * @param  Closure|null $runtimeIdResolver function(string $name, Item $item, string $namespace): string
     * @param  int          $itemsPerTick
     * @param  int          $intervalTicks
     * @param  Closure|null $onComplete
     *
     * @return TaskHandle
     */
    public static function registerAllBatched(
        string $registryClass,
        string $namespace,
        ?Closure $runtimeIdResolver = null,
        int $itemsPerTick = 10,
        int $intervalTicks = 1,
        ?Closure $onComplete = null
    ): TaskHandle {
        return self::getInstance()->registerAllFromBatched(
            $registryClass,
            $namespace,
            $runtimeIdResolver,
            $itemsPerTick,
            $intervalTicks,
            $onComplete
        );
    }

    /**
     * @param  class-string $registryClass
     * @param  Closure|null $runtimeIdResolver function(string $name, Item $item, string $namespace): string
     *
     * @throws ItemRegistryException|ReflectionException
     */
    public function registerAllFrom(string $registryClass, string $namespace, ?Closure $runtimeIdResolver = null): int {
        $registered = 0;
        foreach ($this->registerAllFromGenerator($registryClass, $namespace, $runtimeIdResolver) as $registered) {
        }

        return $registered;
    }

    /**
     * @param  class-string $registryClass
     * @param  Closure|null $runtimeIdResolver function(string $name, Item $item, string $namespace): string
     */
    public function registerAllFromBatched(
        string $registryClass,
        string $namespace,
        ?Closure $runtimeIdResolver = null,
        int $itemsPerTick = 10,
        int $intervalTicks = 1,
        ?Closure $onComplete = null
    ): TaskHandle {
        return Tasks::generator(
            $this->registerAllFromGenerator($registryClass, $namespace, $runtimeIdResolver),
            $itemsPerTick,
            $intervalTicks,
            $onComplete
        );
    }

    /**
     * @param  class-string $registryClass
     * @param  Closure|null $runtimeIdResolver function(string $name, Item $item, string $namespace): string
     *
     * @return Generator<int, int>
     */
    public function registerAllFromGenerator(string $registryClass, string $namespace, ?Closure $runtimeIdResolver = null): Generator {
        if (!class_exists($registryClass)) {
            throw new ItemRegistryException("Item registry class '" . $registryClass . "' does not exist.");
        }

        if (!method_exists($registryClass, "getAll")) {
            throw new ItemRegistryException("Item registry class '" . $registryClass . "' must expose a static getAll() method.");
        }

        $namespace = self::normalizeRuntimePath($namespace);
        if ($namespace === "" || $namespace === "minecraft") {
            throw new ItemRegistryException("Custom item namespace cannot be empty or minecraft.");
        }

        $registered = 0;
        foreach ($registryClass::getAll() as $name => $item) {
            if (!$item instanceof Item) {
                throw new ItemRegistryException("Item registry entry '" . $name . "' must be an Item.");
            }

            $runtimeId = $runtimeIdResolver !== null
                ? $runtimeIdResolver((string) $name, $item, $namespace)
                : $namespace . ":" . self::normalizeRuntimePath((string) $name);

            $this->register($runtimeId, static fn () => clone $item);
            ++$registered;
            yield $registered;
        }

        return $registered;
    }

    /**
     * Registers an item that uses the legacy component format.
     *
     * @param  string $runtimeId
     * @param  Item   $item
     *
     * @throws ReflectionException|ItemRegistryException
     * @return void
     */
    public function registerLegacyItem(string $runtimeId, Item $item): void {
        $this->validateRuntimeId($runtimeId);

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

    /**
     * Registers an item that uses the data-driven component format.
     *
     * @param  string $runtimeId
     * @param  Item   $item
     *
     * @throws ReflectionException|ItemRegistryException
     * @return void
     */
    public function registerDataDrivenItem(string $runtimeId, Item $item): void {
        $this->validateRuntimeId($runtimeId);

        $format = $this->resolveItemFormat($item);
        if ($format !== ItemFormatEnum::DATA_DRIVEN) {
            throw new ItemRegistryException("Item must be a Data-Driven item.");
        }

        $itemBuilder = DataDrivenItemBuilder::create($item)
            ->setRuntimeId($runtimeId)
            ->setTypeId($item->getTypeId());

        $this->applyItemComponents($itemBuilder);
        $this->deepRegister($itemBuilder);
    }

    /**
     * Applies default and user-defined components to the item builder.
     *
     * @param  ItemBuilder $builder
     *
     * @throws ItemRegistryException
     * @return void
     */
    public function applyItemComponents(ItemBuilder $builder): void {
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

    /**
     * Inserts the item into PocketMine serializers, parsers and network dictionaries.
     *
     * @internal Prefer register(), registerLegacyItem() or registerDataDrivenItem().
     *
     * @param  ItemBuilder $builder
     * @return void
     */
    public function deepRegister(ItemBuilder $builder): void {
        $item = $builder->getItem();
        $runtimeId = $builder->getRuntimeId();
        $typeId = $builder->getTypeId();

        if (isset($this->items[$runtimeId])) {
            throw new ItemRegistryException("Item '". $runtimeId . "' is already registered.");
        }

        if (isset($this->typeIds[$typeId])) {
            throw new ItemRegistryException(
                "Item type ID '" . $typeId . "' is already registered as '" . $this->typeIds[$typeId] . "'."
            );
        }

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
            $this->registerItemParserAlias($legacyIdentifier, fn () => clone $item);
        }
        $this->registerItemParserAlias($identifier, fn () => clone $item);
        LegacyItemIdToStringIdMap::getInstance()->add($legacyIdentifier, $item->getTypeId());
        $this->mapBlockItem($builder);

        $this->items[$runtimeId] = $builder;
        $this->typeIds[$typeId] = $runtimeId;
        CreativeInventoryManager::getInstance()->addToCreative($item);
    }

    private function mapBlockItem(ItemBuilder $builder): void {
        $item = $builder->getItem();
        if (!method_exists($item, "getBlock")) {
            return;
        }

        $block = $item->getBlock();
        if (!$block instanceof Block) {
            return;
        }

        $blockBuilder = CustomBlockRegistry::getInstance()->getByBlock($block);
        if ($blockBuilder === null) {
            return;
        }

        $blockItemIdMap = BlockItemIdMap::getInstance();
        $reflection = new ReflectionClass($blockItemIdMap);
        $itemToBlockId = $reflection->getProperty("itemToBlockId");
        /** @var string[] $value */
        $value = $itemToBlockId->getValue($blockItemIdMap);
        $itemToBlockId->setValue($blockItemIdMap, $value + [$builder->getRuntimeId() => $blockBuilder->getRuntimeId()]);
    }

    private function validateRuntimeId(string $runtimeId): void {
        if (trim($runtimeId) === "") {
            throw new InvalidArgumentException("Item runtime ID cannot be empty.");
        }

        if (!str_contains($runtimeId, ":")) {
            throw new InvalidArgumentException("Item runtime ID '{$runtimeId}' must be namespaced.");
        }

        if (str_starts_with($runtimeId, "minecraft:")) {
            throw new InvalidArgumentException("Custom item runtime ID cannot use the minecraft namespace.");
        }
    }

    private function resolveItemFormat(Item $item): ItemFormatEnum {
        try {
            return ItemFormatEnum::fromItem($item);
        } catch (InvalidArgumentException) {
            return ItemFormatEnum::DATA_DRIVEN;
        }
    }

    private static function normalizeRuntimePath(string $value): string {
        $value = strtolower(trim($value));
        $value = preg_replace("/[^a-z0-9_\\.\\/-]+/", "_", $value) ?? "";
        return trim($value, "_");
    }

    private function registerItemParserAlias(string $alias, Closure $factory): void {
        try {
            StringToItemParser::getInstance()->register($alias, $factory);
        } catch (InvalidArgumentException $exception) {
            if (!str_contains($exception->getMessage(), "already registered")) {
                throw $exception;
            }
        }
    }
}
