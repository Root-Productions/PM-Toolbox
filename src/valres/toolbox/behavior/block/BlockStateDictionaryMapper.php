<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;

final class BlockStateDictionaryMapper {
    use SingletonTrait;

    private ReflectionProperty $statesProperty;
    private ReflectionProperty $stateLookupProperty;
    private ReflectionProperty $idMetaCacheProperty;
    private ReflectionProperty $fallbackStateIdProperty;
    private ReflectionProperty $networkIdCacheProperty;

    /** @var array<BlockStateDictionaryEntry> */
    private array $vanillaStates = [];

    /** @var array<BlockStateDictionaryEntry> */
    private array $states = [];

    /**
     * @var array<string, array{builder: BlockBuilder, entries: BlockStateDictionaryEntry[]}>
     */
    private array $blocks = [];

    /**
     * @throws ReflectionException
     */
    public function __construct() {
        $translator = TypeConverter::getInstance()->getBlockTranslator();
        $dictionary = $translator->getBlockStateDictionary();

        $dictionaryReflection = new ReflectionClass($dictionary);
        $translatorReflection = new ReflectionClass($translator);

        $this->statesProperty = $dictionaryReflection->getProperty("states");
        $this->stateLookupProperty = $dictionaryReflection->getProperty("stateDataToStateIdLookup");
        $this->idMetaCacheProperty = $dictionaryReflection->getProperty("idMetaToStateIdLookupCache");
        $this->fallbackStateIdProperty = $translatorReflection->getProperty("fallbackStateId");
        $this->networkIdCacheProperty = $translatorReflection->getProperty("networkIdCache");
        $this->vanillaStates = $dictionary->getStates();
    }

    /**
     * @return array
     */
    public function getStates(): array {
        return $this->states;
    }

    /**
     * Returns block states registered through this mapper.
     *
     * @return array<string, array{builder: BlockBuilder, entries: BlockStateDictionaryEntry[]}>
     */
    public function getBlocks(): array {
        return $this->blocks;
    }

    /**
     * @param BlockStateDictionaryEntry[] $entries
     *
     * @throws ReflectionException
     */
    public function map(BlockBuilder $builder, array $entries): void {
        foreach ($entries as $entry) {
            $this->validateEntry($entry);
        }

        foreach ($entries as $entry) {
            $this->states[$this->entryKey($entry)] = $entry;
        }

        $this->blocks[$builder->getRuntimeId()] = [
            "builder" => $builder,
            "entries" => $entries
        ];

        $this->apply();
    }

    private function validateEntry(BlockStateDictionaryEntry $entry): void {
        if ($entry->getStateName() === "") {
            throw new RuntimeException("Block state must contain a non-empty state name.");
        }
    }

    public static function fnv1a32Nbt(CompoundTag $tag): int {
        if ($tag->getString("name", "") === "minecraft:unknown") {
            return -2;
        }

        $nbtStream = new LittleEndianNbtSerializer();
        $binaryNBT = $nbtStream->write(new TreeRoot($tag));

        return self::fnv1a32($binaryNBT);
    }

    private static function fnv1a32(string $str): int {
        $hashHex = hash('fnv1a32', $str);
        $hashInt = intval(hexdec($hashHex));
        if ($hashInt > 0x7FFFFFFF) {
            $hashInt -= 0x100000000;
        }

        return $hashInt;
    }

    private function sort(array $states, ?array &$sortedStates, ?array &$stateDataToStateIdLookup): void {
        if ($stateDataToStateIdLookup === null) {
            $stateDataToStateIdLookup = [];
        }
        if ($sortedStates === null) {
            $sortedStates = [];
        }

        foreach ($states as $name => $blockStates) {
            $numberState = count($blockStates);

            foreach ($blockStates as $_ => $blockState) {
                $data = BlockStateDictionaryEntry::decodeStateProperties($blockState->getRawStateProperties());
                ksort($data);

                $test = CompoundTag::create();
                foreach (Utils::stringifyKeys($data) as $key => $state) {
                    $test->setTag($key, $state);
                }

                $tag = CompoundTag::create()
                    ->setString("name", $blockState->getStateName())
                    ->setTag("states", $test);

                $stateId = self::fnv1a32Nbt($tag);
                if ($numberState === 1) {
                    $stateDataToStateIdLookup[$name] = $stateId;
                } else {
                    $stateDataToStateIdLookup[$name][$blockState->getRawStateProperties()] = $stateId;
                }

                $sortedStates[$stateId] = $blockState;
            }
        }
    }

    /** @throws ReflectionException */
    public function apply(): void {
        $translator = TypeConverter::getInstance()->getBlockTranslator();
        $dictionary = $translator->getBlockStateDictionary();
        $states = [];

        foreach ($this->vanillaStates as $state) {
            $states[$state->getStateName()][] = $state;
        }

        foreach ($this->getStates() as $state) {
            $states[$state->getStateName()][] = $state;
        }

        $sortedStates = [];
        $stateDataToStateIdLookupValue = [];
        $this->sort($states, $sortedStates, $stateDataToStateIdLookupValue);
        $this->statesProperty->setValue($dictionary, $sortedStates);
        $this->stateLookupProperty->setValue($dictionary, $stateDataToStateIdLookupValue);
        $this->idMetaCacheProperty->setValue($dictionary, null);
        $this->networkIdCacheProperty->setValue($translator, []);
        $this->fallbackStateIdProperty->setValue(
            $translator,
            $stateDataToStateIdLookupValue[BlockTypeNames::INFO_UPDATE] ??
            throw new AssumptionFailedError(BlockTypeNames::INFO_UPDATE . " should always exist")
        );
    }

    private function entryKey(BlockStateDictionaryEntry $entry): string {
        return $entry->getStateName() . "\0" . $entry->getRawStateProperties();
    }
}
