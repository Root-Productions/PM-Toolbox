<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use ReflectionException;
use RuntimeException;
use valres\toolbox\behavior\block\component\BlockComponent;
use valres\toolbox\behavior\block\permutation\BlockPermutation;
use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\BlockTrait;
use valres\toolbox\behavior\exception\ItemRegistryException;
use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;
use valres\toolbox\behavior\item\builder\ItemBuilder;
use valres\toolbox\behavior\item\builder\LegacyItemBuilder;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;
use valres\toolbox\behavior\item\ItemFormatEnum;

/** Collects the runtime metadata, components, states and traits of a custom block. */
final class BlockBuilder {
    private Block $block;
    private ?Closure $blockFactory = null;

    private string $runtimeId;
    private int $typeId;

    private ?Closure $deserializer = null;
    private ?Closure $serializer = null;

    /** @var array<string> */
    private array $tags = [];

    /** @var array<string, BlockComponent> */
    private array $components = [];

    /** @var array<BlockPermutation> */
    private array $permutations = [];

    /** @var array<BlockStateProperty> */
    private array $properties = [];

    /** @var array<string, BlockTrait> */
    private array $traits = [];

    public function __construct(Block $block) {
        $this->block = $block;
    }

    public static function create(Block $block): self {
        return new self($block);
    }

    public function getName(): string
    {
        $itemName = $this->getRuntimeId();
        if (str_contains($itemName, ":")) {
            [, $itemName] = explode(":", $itemName, 2);
        }

        return $itemName;
    }

    public function getBlock(): Block {
        return $this->block;
    }

    public function setBlock(Block $block): void {
        $this->block = $block;
    }

    public function getBlockFactory(): Closure {
        if ($this->blockFactory !== null) {
            return $this->blockFactory;
        }

        $block = $this->block;
        return static fn() => clone $block;
    }

    public function setBlockFactory(?Closure $blockFactory): self {
        $this->blockFactory = $blockFactory;
        return $this;
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

    public function getOrCreateTypeId(): int {
        return $this->typeId ??= BlockTypeIds::newId();
    }

    public function getDeserializer(): ?Closure {
        return $this->deserializer;
    }

    public function setDeserializer(?Closure $deserializer): self {
        $this->deserializer = $deserializer;
        return $this;
    }

    public function getSerializer(): ?Closure {
        return $this->serializer;
    }

    public function setSerializer(?Closure $serializer): self {
        $this->serializer = $serializer;
        return $this;
    }

    /** @return list<string> */
    public function getTags(): array {
        return $this->tags;
    }

    public function addTag(string $tag): void {
        if (in_array($tag, $this->tags)) {
            return;
        }

        $this->tags[] = $tag;
    }

    /** @return array<string, BlockComponent> */
    public function getComponents(): array {
        return $this->components;
    }

    public function getComponent(string $id): ?BlockComponent {
        return $this->components[$id] ?? null;
    }

    public function asComponent(string $id): bool {
        return $this->getComponent($id) !== null;
    }

    public function addComponent(BlockComponent $component): self {
        $this->components[$component->getIdentifier()] = $component;
        return $this;
    }

    public function removeComponent(string $id): void {
        unset($this->components[$id]);
    }

    /** @return list<BlockPermutation> */
    public function getPermutations(): array {
        return $this->permutations;
    }

    public function addPermutation(BlockPermutation $permutation): self {
        $this->permutations[] = $permutation;
        return $this;
    }

    /** @return list<BlockStateProperty> */
    public function getProperties(): array {
        return $this->properties;
    }

    public function hasProperty(string $name): bool {
        foreach ($this->properties as $property) {
            if ($property->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    public function addProperty(BlockStateProperty $property): self {
        $this->properties[] = $property;
        return $this;
    }

    /** @return array<string, BlockTrait> */
    public function getTraits(): array {
        return $this->traits;
    }

    public function getTrait(string $id): ?BlockTrait {
        return $this->traits[$id] ?? null;
    }

    public function hasTrait(string $id): bool {
        return $this->getTrait($id) !== null;
    }

    public function addTrait(BlockTrait $trait): self {
        $this->traits[$trait->getIdentifier()] = $trait;
        return $this;
    }

    public function removeTrait(string $id): void {
        unset($this->traits[$id]);
    }

    /** @throws ReflectionException|ItemRegistryException */
    public function getItemBuilder(): ItemBuilder {
        $item = $this->getBlock()->asItem();
        if ($item->isNull()) {
            throw new RuntimeException("Block must have a valid item form to be registered.");
        }

        $itemFormat = ItemFormatEnum::fromItem($item);
        $builder = match ($itemFormat) {
            ItemFormatEnum::LEGACY => LegacyItemBuilder::create($item),
            ItemFormatEnum::DATA_DRIVEN => DataDrivenItemBuilder::create($item),
        };

        $builder
            ->setRuntimeId($this->runtimeId)
            ->setTypeId($item->getTypeId());

        return $builder;
    }

    public function toNBT(): CompoundTag {
        $components = CompoundTag::create();
        foreach ($this->components as $id => $component) {
            $components->setTag($id, $component->toNBT());
        }

        $properties = [];
        $traitPropertyNames = [];
        foreach ($this->getTraitProperties() as $property) {
            $traitPropertyNames[$property->getName()] = true;
        }

        foreach ($this->properties as $property) {
            if (isset($traitPropertyNames[$property->getName()])) {
                continue;
            }

            $properties[] = $property->toNBT();
        }

        return CompoundTag::create()
            ->setTag("components", $components)
            ->setTag("permutations", new ListTag(
                array_map(fn (BlockPermutation $permutation) => $permutation->toNBT(), $this->permutations),
                NBT::TAG_Compound
            ))
            ->setTag("properties", new ListTag($properties, NBT::TAG_Compound))
            ->setTag("traits", new ListTag(
                array_map(fn (BlockTrait $trait) => $trait->toNBT(), $this->traits),
                NBT::TAG_Compound
            ))
            ->setTag("menu_category", CompoundTag::create()
                ->setTag("category", new StringTag("none"))
                ->setTag("group", new StringTag("none"))
                ->setTag("is_hidden_in_commands", new ByteTag(0))
            )
            ->setTag("blockTags", new ListTag(
                array_map(fn (string $tag) => new StringTag($tag), $this->tags),
                NBT::TAG_String
            ))
            ->setTag("vanilla_block_data", CompoundTag::create()
                ->setTag("block_id", new IntTag(BlockPaletteEntryMapper::getInstance()->nextRuntimeId()))
                ->setTag("material", new StringTag("dirt"))
            )
            ->setTag("molangVersion", new IntTag(13));
    }

    /** @return list<BlockStateDictionaryEntry> */
    public function getBlockStateDictionaryEntries(): array {
        $properties = $this->getAllStateProperties();
        if (empty($properties)) {
            return [new BlockStateDictionaryEntry($this->getRuntimeId(), [], 0)];
        }

        $propertyNames = array_map(static fn(BlockStateProperty $p) => $p->getName(), $properties);
        $propertyValues = array_map(static fn(BlockStateProperty $p) => $p->getValues(), $properties);
        $entries = [];
        foreach ($this->getCartesianProduct($propertyValues) as $k => $properties) {
            $states = [];
            foreach ($properties as $i => $data) {
                $states[$propertyNames[$i]] = ComponentNbtHelper::tag($data);
            }

            $entries[] = new BlockStateDictionaryEntry($this->getRuntimeId(), $states, $k);
        }

        return $entries;
    }

    /** @return list<BlockStateProperty> */
    private function getAllStateProperties(): array {
        $properties = [];
        foreach ([...$this->properties, ...$this->getTraitProperties()] as $property) {
            $properties[$property->getName()] = $property;
        }

        return array_values($properties);
    }

    /** @return list<BlockStateProperty> */
    private function getTraitProperties(): array {
        $properties = [];
        foreach ($this->traits as $trait) {
            foreach ($trait->getProvidedProperties() as $property) {
                $properties[$property->getName()] = $property;
            }
        }

        return array_values($properties);
    }

    /**
     * @internal Generates every combination of block state values.
     *
     * @param list<list<mixed>> $arrays
     * @return list<list<mixed>>
     */
    public function getCartesianProduct(array $arrays): array {
        if (empty($arrays)) {
            return [[]];
        }

        foreach ($arrays as $array) {
            if (empty($array)) {
                return [];
            }
        }

        $result = [];
        $counts = array_map('count', $arrays);
        $combinations = array_product($counts);
        $count = count($arrays);
        $indices = array_fill(0, $count, 0);

        for ($i = 0; $i < $combinations; $i++) {
            $combination = [];
            for ($k = 0; $k < $count; $k++) {
                $combination[] = $arrays[$k][$indices[$k]];
            }
            $result[] = $combination;

            for ($j = $count - 1; $j >= 0; $j--) {
                $indices[$j]++;
                if ($indices[$j] < $counts[$j]) {
                    break;
                }
                $indices[$j] = 0;
            }
        }

        return $result;
    }
}
