<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use Closure;
use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\inventory\CreativeCategory;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use valres\toolbox\behavior\attribute\CreativeInventoryInfo;
use valres\toolbox\behavior\block\task\AsyncRegisterBlocksTask;
use valres\toolbox\behavior\creative\CreativeInventoryManager;
use valres\toolbox\behavior\exception\BlockRegistryException;
use valres\toolbox\behavior\exception\ItemRegistryException;
use valres\toolbox\behavior\item\CustomItemRegistry;
use valres\toolbox\behavior\item\ItemFormatEnum;
use valres\toolbox\behavior\item\ItemTypeDictionaryMapper;
use valres\toolbox\ToolboxLoader;

final class CustomBlockRegistry {
    use SingletonTrait;

    /** @var array<string, BlockBuilder> */
    private array $blocks = [];

    private bool $asyncWorkerHookRegistered = false;

    /**
     * Registers a custom block and all associated network/runtime mappings.
     * The closure may either accept the allocated block type id or no parameter.
     *
     * @throws BlockRegistryException|ReflectionException|ItemRegistryException
     */
    public function register(string $runtimeId, Closure $blockClosure): BlockBuilder {
        $typeId = BlockTypeIds::newId();
        $block = $this->createBlock($blockClosure, $typeId);
        if (!$block instanceof Block) {
            throw new BlockRegistryException("Block closure must return a Block.");
        }

        $builder = BlockBuilder::create($block)
            ->setRuntimeId($runtimeId)
            ->setTypeId($block->getTypeId() > 0 ? $block->getTypeId() : $typeId)
            ->setBlockFactory($this->createBlockFactory($blockClosure, $typeId));

        $this->deepRegister($builder);

        return $builder;
    }

    /**
     * Registers an already configured block builder.
     *
     * @throws BlockRegistryException|ReflectionException|ItemRegistryException
     */
    public function registerBuilder(BlockBuilder $builder): void {
        $this->deepRegister($builder);
    }

    /**
     * Inserts the block into PocketMine serializers, parsers and network dictionaries.
     *
     * @internal Prefer register().
     *
     * @throws ReflectionException|ItemRegistryException
     */
    public function deepRegister(BlockBuilder $builder): void {
        $runtimeId = $builder->getRuntimeId();
        $block = $builder->getBlock();
        $this->validateRuntimeId($runtimeId);
        if (isset($this->blocks[$runtimeId])) {
            throw new InvalidArgumentException("Block '{$runtimeId}' is already registered.");
        }

        BlockDataResolver::applyDefault($builder);

        $serializer = $builder->getSerializer() ?? static fn () => new BlockStateWriter($runtimeId);
        $deserializer = $builder->getDeserializer() ?? static fn (BlockStateReader $in) => clone $block;
        $entries = $builder->getBlockStateDictionaryEntries();

        foreach ($entries as $entry) {
            GlobalBlockStateHandlers::getUpgrader()->getBlockIdMetaUpgrader()->addIdMetaToStateMapping($entry->getStateName(), $entry->getMeta(), $entry->generateStateData());
        }

        RuntimeBlockStateRegistry::getInstance()->register($block);
        GlobalBlockStateHandlers::getDeserializer()->map($runtimeId, $deserializer);
        GlobalBlockStateHandlers::getSerializer()->map($block, $serializer);

        BlockPaletteEntryMapper::getInstance()->map($builder, new BlockPaletteEntry($runtimeId, new CacheableNbt($builder->toNBT())));
        BlockStateDictionaryMapper::getInstance()->map($builder, $entries);

        $this->registerBlockItem($builder);
        $this->blocks[$runtimeId] = $builder;
        AsyncBlockRegistrationStore::add($builder);

        if (ToolboxLoader::isLoaded()) {
            $this->syncAsyncWorkers(ToolboxLoader::getLoader());
        }
    }

    /**
     * Registers custom blocks in async workers so async tasks share the main thread block mappings.
     */
    public function syncAsyncWorkers(PluginBase $plugin): void {
        $pool = $plugin->getServer()->getAsyncPool();
        if (!$this->asyncWorkerHookRegistered && method_exists($pool, "addWorkerStartHook")) {
            $this->asyncWorkerHookRegistered = true;
            $pool->addWorkerStartHook(static function(int $worker) use ($pool): void {
                $definitions = AsyncBlockRegistrationStore::getAll();
                if ($definitions !== []) {
                    $pool->submitTaskToWorker(new AsyncRegisterBlocksTask($definitions), $worker);
                }
            });
        }

        $definitions = AsyncBlockRegistrationStore::getAll();
        if ($definitions === [] || !method_exists($pool, "submitTaskToWorker")) {
            return;
        }

        if (method_exists($pool, "getSize")) {
            for ($worker = 0; $worker < $pool->getSize(); $worker++) {
                $pool->submitTaskToWorker(new AsyncRegisterBlocksTask($definitions), $worker);
            }
            return;
        }

        $pool->submitTask(new AsyncRegisterBlocksTask($definitions));
    }

    /**
     * Registers the block pieces required inside async worker threads.
     *
     * @internal Called by AsyncRegisterBlocksTask.
     *
     * @throws ReflectionException
     */
    public function registerWorker(BlockBuilder $builder): void {
        $runtimeId = $builder->getRuntimeId();
        if (isset($this->blocks[$runtimeId])) {
            return;
        }

        $block = $builder->getBlock();
        $serializer = $builder->getSerializer() ?? static fn () => new BlockStateWriter($runtimeId);
        $deserializer = $builder->getDeserializer() ?? static fn (BlockStateReader $in) => clone $block;
        $entries = $builder->getBlockStateDictionaryEntries();

        foreach ($entries as $entry) {
            GlobalBlockStateHandlers::getUpgrader()->getBlockIdMetaUpgrader()->addIdMetaToStateMapping($entry->getStateName(), $entry->getMeta(), $entry->generateStateData());
        }

        RuntimeBlockStateRegistry::getInstance()->register($block);
        GlobalBlockStateHandlers::getDeserializer()->map($runtimeId, $deserializer);
        GlobalBlockStateHandlers::getSerializer()->map($block, $serializer);
        BlockStateDictionaryMapper::getInstance()->map($builder, $entries);

        $this->registerBlockParserAlias($builder->getName(), fn () => clone $block);
        $this->registerBlockParserAlias($runtimeId, fn () => clone $block);

        $this->blocks[$runtimeId] = $builder;
    }

    /** @throws BlockRegistryException|ReflectionException|ItemRegistryException */
    private function registerBlockItem(BlockBuilder $builder): void {
        $block = $builder->getBlock();
        $item = $block->asItem();
        if ($item->isNull()) {
            throw new BlockRegistryException("Block must have a valid item form to be registered.");
        }

        $name = $builder->getName();
        $stringId = $builder->getRuntimeId();

        if ($item instanceof ItemBlock) {
            ItemTypeDictionaryMapper::getInstance()->registerEntry(new ItemTypeEntry(
                $stringId,
                $block->getTypeId(),
                false,
                ItemFormatEnum::NONE->value,
                new CacheableNbt(CompoundTag::create())
            ));

            GlobalItemDataHandlers::getDeserializer()->map($stringId, fn(SavedItemData $data) => clone $item);
            GlobalItemDataHandlers::getSerializer()->map($item, fn() => new SavedItemData($stringId));
        } else {
            CustomItemRegistry::getInstance()->deepRegister($builder->getItemBuilder());
        }

        $this->registerBlockParserAlias($name, fn () => clone $block);
        $this->registerBlockParserAlias($stringId, fn () => clone $block);
        LegacyItemIdToStringIdMap::getInstance()->add($name, 255 - $builder->getTypeId());

        $blockItemIdMap = BlockItemIdMap::getInstance();
        $reflection = new ReflectionClass($blockItemIdMap);

        $itemToBlockId = $reflection->getProperty("itemToBlockId");
        /** @var string[] $value */
        $value = $itemToBlockId->getValue($blockItemIdMap);
        $itemToBlockId->setValue($blockItemIdMap, $value + [$stringId => $stringId]);

        $creativeInfo = $this->readCreativeInfo($block) ?? $this->readCreativeInfo($item);
        if ($creativeInfo !== null && !$creativeInfo->isHidden()) {
            CreativeInventoryManager::getInstance()->add(
                $item,
                $creativeInfo->getCategory() ?? CreativeCategory::ITEMS,
                $creativeInfo->getGroup()
            );
        }
    }

    public function get(string $runtimeId): ?BlockBuilder {
        return $this->blocks[$runtimeId] ?? null;
    }

    /**
     * @return array<string, BlockBuilder>
     */
    public function getAll(): array {
        return $this->blocks;
    }

    private function validateRuntimeId(string $runtimeId): void {
        if (trim($runtimeId) === "") {
            throw new InvalidArgumentException("Block runtime ID cannot be empty.");
        }

        if (!str_contains($runtimeId, ":")) {
            throw new InvalidArgumentException("Block runtime ID '{$runtimeId}' must be namespaced.");
        }

        if (str_starts_with($runtimeId, "minecraft:")) {
            throw new InvalidArgumentException("Custom block runtime ID cannot use the minecraft namespace.");
        }
    }

    private function createBlock(Closure $blockClosure, int $typeId): mixed {
        $reflection = new ReflectionFunction($blockClosure);
        return $reflection->getNumberOfParameters() > 0 ? $blockClosure($typeId) : $blockClosure();
    }

    private function createBlockFactory(Closure $blockClosure, int $typeId): Closure {
        $reflection = new ReflectionFunction($blockClosure);
        return $reflection->getNumberOfParameters() > 0
            ? static fn() => $blockClosure($typeId)
            : static fn() => $blockClosure();
    }

    private function readCreativeInfo(object $object): ?CreativeInventoryInfo {
        $attributes = (new ReflectionClass($object))->getAttributes(CreativeInventoryInfo::class);
        return $attributes === [] ? null : $attributes[0]->newInstance();
    }

    private function registerBlockParserAlias(string $alias, \Closure $factory): void {
        try {
            StringToItemParser::getInstance()->registerBlock($alias, $factory);
        } catch (InvalidArgumentException $exception) {
            if (!str_contains($exception->getMessage(), "already registered")) {
                throw $exception;
            }
        }
    }
}
