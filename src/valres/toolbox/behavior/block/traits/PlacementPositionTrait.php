<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits;

use BackedEnum;
use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\type\BlockTraitId;
use valres\toolbox\behavior\block\traits\type\PlacementPositionTraitState;

/** Derives face and vertical-half states from the placement position. */
final class PlacementPositionTrait extends BlockTrait {
    /** @param list<PlacementPositionTraitState|string> $enabledStates */
    public function __construct(private readonly array $enabledStates) {
    }

    public static function blockFace(): self {
        return new self([PlacementPositionTraitState::BLOCK_FACE]);
    }

    public static function verticalHalf(): self {
        return new self([PlacementPositionTraitState::VERTICAL_HALF]);
    }

    public static function all(): self {
        return new self([PlacementPositionTraitState::BLOCK_FACE, PlacementPositionTraitState::VERTICAL_HALF]);
    }

    public static function identifier(): string {
        return BlockTraitId::PLACEMENT_POSITION->value;
    }

    protected function enabledStates(): array {
        return $this->enabledStates;
    }

    public function getProvidedProperties(): array {
        $properties = [];
        foreach ($this->enabledStates as $state) {
            $state = $state instanceof BackedEnum ? $state->value : $state;
            if ($state === PlacementPositionTraitState::BLOCK_FACE->value) {
                $properties[] = new BlockStateProperty("minecraft:block_face", ["down", "up", "south", "north", "west", "east"]);
            }

            if ($state === PlacementPositionTraitState::VERTICAL_HALF->value) {
                $properties[] = new BlockStateProperty("minecraft:vertical_half", ["bottom", "top"]);
            }
        }

        return $properties;
    }
}
