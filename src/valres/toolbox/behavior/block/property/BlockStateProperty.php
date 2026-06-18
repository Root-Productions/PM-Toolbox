<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\property;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

/** Defines a Bedrock block state name and its allowed values. */
final class BlockStateProperty {
    const TAG_NAME = "name";
    const TAG_ENUM = "enum";

    public function __construct(
        private readonly string $name,
        private readonly array $values
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    /** @return list<mixed> */
    public function getValues(): array {
        return $this->values;
    }

    public function toNBT(): CompoundTag {
        return CompoundTag::create()
            ->setTag(self::TAG_NAME, new StringTag($this->name))
            ->setTag(self::TAG_ENUM, new ListTag(array_map(
                static fn(mixed $value) => ComponentNbtHelper::tag($value),
                array_values($this->values)
            )));
    }
}
