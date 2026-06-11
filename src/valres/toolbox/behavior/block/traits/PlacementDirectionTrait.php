<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits;

use BackedEnum;
use valres\toolbox\behavior\block\property\BlockStateProperty;
use valres\toolbox\behavior\block\traits\type\BlockDescriptor;
use valres\toolbox\behavior\block\traits\type\BlockTraitId;
use valres\toolbox\behavior\block\traits\type\PlacementDirectionTraitState;

final class PlacementDirectionTrait extends BlockTrait {
    /**
     * @param list<PlacementDirectionTraitState|string> $enabledStates
     * @param list<BlockDescriptor|string|array>        $blocksToCornerWith
     */
    public function __construct(
        private readonly array $enabledStates,
        private readonly ?int $yRotationOffset = null,
        private readonly array $blocksToCornerWith = []
    ) {
    }

    public static function cardinal(?int $yRotationOffset = null): self {
        return new self([PlacementDirectionTraitState::CARDINAL_DIRECTION], $yRotationOffset);
    }

    public static function facing(?int $yRotationOffset = null): self {
        return new self([PlacementDirectionTraitState::FACING_DIRECTION], $yRotationOffset);
    }

    /** @param list<BlockDescriptor|string|array> $blocksToCornerWith */
    public static function cornerAndCardinal(array $blocksToCornerWith = [], ?int $yRotationOffset = null): self {
        return new self([PlacementDirectionTraitState::CORNER_AND_CARDINAL_DIRECTION], $yRotationOffset, $blocksToCornerWith);
    }

    public static function identifier(): string {
        return BlockTraitId::PLACEMENT_DIRECTION->value;
    }

    protected function enabledStates(): array {
        return $this->enabledStates;
    }

    protected function values(): array {
        return [
            "y_rotation_offset" => $this->yRotationOffset,
            "blocks_to_corner_with" => $this->blocksToCornerWith === [] ? null : BlockDescriptor::listToArray($this->blocksToCornerWith)
        ];
    }

    public function getProvidedProperties(): array {
        $properties = [];
        foreach ($this->enabledStates as $state) {
            $state = $state instanceof BackedEnum ? $state->value : $state;
            if ($state === PlacementDirectionTraitState::CARDINAL_DIRECTION->value) {
                $properties["minecraft:cardinal_direction"] = new BlockStateProperty("minecraft:cardinal_direction", ["south", "north", "west", "east"]);
            }

            if ($state === PlacementDirectionTraitState::FACING_DIRECTION->value) {
                $properties["minecraft:facing_direction"] = new BlockStateProperty("minecraft:facing_direction", ["down", "up", "south", "north", "west", "east"]);
            }

            if ($state === PlacementDirectionTraitState::CORNER_AND_CARDINAL_DIRECTION->value) {
                $properties["minecraft:cardinal_direction"] = new BlockStateProperty("minecraft:cardinal_direction", ["south", "north", "west", "east"]);
                $properties["minecraft:corner"] = new BlockStateProperty("minecraft:corner", ["none", "inner_left", "inner_right", "outer_left", "outer_right"]);
            }
        }

        return array_values($properties);
    }
}
