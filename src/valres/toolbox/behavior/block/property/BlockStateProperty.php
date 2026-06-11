<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\property;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

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

    public function getValues(): array {
        return $this->values;
    }

    public function toNBT(): CompoundTag {
        return CompoundTag::create()
            ->setTag(self::TAG_NAME, new StringTag($this->name))
            ->setTag(self::TAG_ENUM, ComponentNbtHelper::compoundList(
                $this->values
            ));
    }
}
