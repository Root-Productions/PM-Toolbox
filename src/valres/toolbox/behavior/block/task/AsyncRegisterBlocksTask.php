<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\task;

use JsonException;
use pmmp\thread\ThreadSafeArray;
use pocketmine\block\Block;
use pocketmine\scheduler\AsyncTask;
use ReflectionException;
use valres\toolbox\behavior\block\AsyncBlockRegistrationStore;
use valres\toolbox\behavior\block\BlockBuilder;
use valres\toolbox\behavior\block\component\RawBlockComponent;
use valres\toolbox\behavior\block\CustomBlockRegistry;
use valres\toolbox\behavior\block\permutation\BlockPermutation;
use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\RawBlockTrait;

final class AsyncRegisterBlocksTask extends AsyncTask {
    private ThreadSafeArray $typeIds;
    private ThreadSafeArray $blocks;
    private ThreadSafeArray $properties;
    private ThreadSafeArray $components;
    private ThreadSafeArray $traits;
    private ThreadSafeArray $permutations;
    private ThreadSafeArray $serializers;
    private ThreadSafeArray $deserializers;

    public function __construct(array $definitions) {
        $this->typeIds = new ThreadSafeArray();
        $this->blocks = new ThreadSafeArray();
        $this->properties = new ThreadSafeArray();
        $this->components = new ThreadSafeArray();
        $this->traits = new ThreadSafeArray();
        $this->permutations = new ThreadSafeArray();
        $this->serializers = new ThreadSafeArray();
        $this->deserializers = new ThreadSafeArray();

        foreach ($definitions as $runtimeId => $definition) {
            $this->typeIds[$runtimeId] = $definition["typeId"];
            $this->blocks[$runtimeId] = $definition["block"];
            $this->properties[$runtimeId] = $definition["properties"];
            $this->components[$runtimeId] = $definition["components"];
            $this->traits[$runtimeId] = $definition["traits"];
            $this->permutations[$runtimeId] = $definition["permutations"];
            $this->serializers[$runtimeId] = $definition["serializer"];
            $this->deserializers[$runtimeId] = $definition["deserializer"];
        }
    }

    /** @throws ReflectionException|JsonException */
    public function onRun(): void {
        self::preloadToolboxClasses();

        foreach ($this->blocks as $runtimeId => $blockFactory) {
            $block = $blockFactory();
            if (!$block instanceof Block) {
                throw new \RuntimeException("Async block definition '{$runtimeId}' did not create a Block.");
            }

            $builder = BlockBuilder::create($block)
                ->setRuntimeId((string) $runtimeId)
                ->setTypeId((int) $this->typeIds[$runtimeId])
                ->setSerializer($this->serializers[$runtimeId])
                ->setDeserializer($this->deserializers[$runtimeId]);

            foreach (json_decode((string) $this->properties[$runtimeId], true, flags: JSON_THROW_ON_ERROR) as $property) {
                $builder->addProperty(new BlockStateProperty($property["name"], $property["values"]));
            }

            foreach (json_decode((string) $this->components[$runtimeId], true, flags: JSON_THROW_ON_ERROR) as $component) {
                $builder->addComponent(new RawBlockComponent(
                    $component["identifier"],
                    AsyncBlockRegistrationStore::decodeComponentNbt($component["nbt"])
                ));
            }

            foreach (json_decode((string) $this->traits[$runtimeId], true, flags: JSON_THROW_ON_ERROR) as $trait) {
                $nbt = AsyncBlockRegistrationStore::decodeComponentNbt($trait["nbt"]);
                if (!$nbt instanceof \pocketmine\nbt\tag\CompoundTag) {
                    throw new \RuntimeException("Async block trait '{$trait["identifier"]}' did not decode to a compound tag.");
                }

                $builder->addTrait(new RawBlockTrait($trait["identifier"], $nbt));
            }

            foreach (json_decode((string) $this->permutations[$runtimeId], true, flags: JSON_THROW_ON_ERROR) as $permutation) {
                $blockPermutation = new BlockPermutation($permutation["condition"]);
                foreach ($permutation["components"] as $component) {
                    $blockPermutation->addComponent(new RawBlockComponent(
                        $component["identifier"],
                        AsyncBlockRegistrationStore::decodeComponentNbt($component["nbt"])
                    ));
                }

                $builder->addPermutation($blockPermutation);
            }

            CustomBlockRegistry::getInstance()->registerWorker($builder);
        }
    }

    private static function preloadToolboxClasses(): void {
        if (class_exists(BlockBuilder::class) && class_exists(CustomBlockRegistry::class)) {
            return;
        }

        $blockDirectory = dirname(__DIR__);
        foreach ([
            "component/BlockComponent.php",
            "component/ComponentNbtHelper.php",
            "component/RawBlockComponent.php",
            "property/BlockStateProperty.php",
            "permutation/BlockPermutation.php",
            "traits/BlockTrait.php",
            "traits/RawBlockTrait.php",
            "BlockBuilder.php",
            "builder/BlockBuilder.php",
            "BlockStateDictionaryMapper.php",
            "CustomBlockRegistry.php",
            "AsyncBlockRegistrationStore.php"
        ] as $file) {
            $path = $blockDirectory . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $file);
            if (is_file($path)) {
                require_once $path;
            }
        }
    }
}
