<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits;

use InvalidArgumentException;
use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\type\BlockTraitId;
use valres\toolbox\behavior\block\traits\type\MultiBlockDirection;
use valres\toolbox\behavior\block\traits\type\MultiBlockTraitState;

/** Describes a block composed of multiple vertically linked parts. */
final class MultiBlockTrait extends BlockTrait {
    public function __construct(
        private readonly MultiBlockDirection|string $direction,
        private readonly int $parts
    ) {
        if ($this->parts < 2 || $this->parts > 4) {
            throw new InvalidArgumentException("Block trait 'minecraft:multi_block' expects parts between 2 and 4, got " . $this->parts);
        }
    }

    public static function identifier(): string {
        return BlockTraitId::MULTI_BLOCK->value;
    }

    protected function enabledStates(): array {
        return [MultiBlockTraitState::MULTI_BLOCK_PART];
    }

    protected function values(): array {
        return [
            "direction" => $this->direction instanceof MultiBlockDirection ? $this->direction->value : $this->direction,
            "parts" => $this->parts
        ];
    }

    public function getProvidedProperties(): array {
        return [
            new BlockStateProperty("minecraft:multi_block_part", range(0, $this->parts - 1))
        ];
    }
}
