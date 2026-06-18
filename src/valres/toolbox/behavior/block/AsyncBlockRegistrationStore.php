<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use JsonException;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use valres\toolbox\behavior\block\component\BlockComponent;
use valres\toolbox\behavior\block\permutation\BlockPermutation;
use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\BlockTrait;

/**
 * Stores serializable block definitions for synchronization with async workers.
 *
 * @internal Used by CustomBlockRegistry and AsyncRegisterBlocksTask.
 */
final class AsyncBlockRegistrationStore {
    private static array $definitions = [];

    /**
     * @internal Called after a block has been registered on the main thread.
     *
     * @throws JsonException
     */
    public static function add(BlockBuilder $builder): void {
        $runtimeId = $builder->getRuntimeId();
        $blockFactory = $builder->getBlockFactory();

        self::$definitions[$builder->getRuntimeId()] = [
            "typeId" => $builder->getTypeId(),
            "properties" => json_encode(array_map(static fn(BlockStateProperty $property) => [
                "name" => $property->getName(),
                "values" => $property->getValues()
            ], $builder->getProperties()), JSON_THROW_ON_ERROR),
            "block" => $blockFactory,
            "components" => json_encode(array_map(static fn(BlockComponent $component) => [
                "identifier" => $component->getIdentifier(),
                "nbt" => base64_encode((new CacheableNbt($component->toNBT()))->getEncodedNbt())
            ], $builder->getComponents()), JSON_THROW_ON_ERROR),
            "traits" => json_encode(array_map(static fn(BlockTrait $trait) => [
                "identifier" => $trait->getIdentifier(),
                "nbt" => base64_encode((new CacheableNbt($trait->toNBT()))->getEncodedNbt())
            ], $builder->getTraits()), JSON_THROW_ON_ERROR),
            "permutations" => json_encode(array_map(static fn(BlockPermutation $permutation) => [
                "condition" => $permutation->getCondition(),
                "components" => array_map(static fn(BlockComponent $component) => [
                    "identifier" => $component->getIdentifier(),
                    "nbt" => base64_encode((new CacheableNbt($component->toNBT()))->getEncodedNbt())
                ], $permutation->getComponents())
            ], $builder->getPermutations()), JSON_THROW_ON_ERROR),
            "serializer" => $builder->getSerializer() ?? static fn() => new BlockStateWriter($runtimeId),
            "deserializer" => $builder->getDeserializer() ?? static fn(BlockStateReader $reader) => $blockFactory()
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public static function getAll(): array {
        return self::$definitions;
    }

    /** @internal Decodes component and trait payloads inside async workers. */
    public static function decodeComponentNbt(string $encoded): \pocketmine\nbt\tag\Tag {
        return (new NetworkNbtSerializer())
            ->read(base64_decode($encoded, true) ?: "")
            ->getTag();
    }
}
