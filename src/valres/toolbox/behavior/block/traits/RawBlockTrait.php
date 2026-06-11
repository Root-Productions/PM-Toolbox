<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits;

use pocketmine\nbt\tag\CompoundTag;

final class RawBlockTrait extends BlockTrait {
    public function __construct(
        private readonly string $identifier,
        private readonly CompoundTag $nbt
    ) {
    }

    public static function identifier(): string {
        return "toolbox:raw_trait";
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    protected function enabledStates(): array {
        return [];
    }

    public function toNBT(): CompoundTag {
        return clone $this->nbt;
    }
}
