<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits;

use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\type\BlockTraitId;
use valres\toolbox\behavior\block\traits\type\ConnectionTraitState;

/** Adds cardinal connection states used by fence-like blocks. */
final class ConnectionTrait extends BlockTrait {
    public static function identifier(): string {
        return BlockTraitId::CONNECTION->value;
    }

    protected function enabledStates(): array {
        return [ConnectionTraitState::CARDINAL_CONNECTIONS];
    }

    public function getProvidedProperties(): array {
        return [
            new BlockStateProperty("minecraft:connection_north", [false, true]),
            new BlockStateProperty("minecraft:connection_south", [false, true]),
            new BlockStateProperty("minecraft:connection_west", [false, true]),
            new BlockStateProperty("minecraft:connection_east", [false, true])
        ];
    }
}
