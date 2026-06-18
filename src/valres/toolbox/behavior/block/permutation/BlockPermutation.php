<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\permutation;

use pocketmine\nbt\tag\CompoundTag;
use valres\toolbox\behavior\block\component\BlockComponent;

/** Applies component overrides when a Molang block-state condition matches. */
final class BlockPermutation {
    const TAG_CONDITION = "condition";
    const TAG_COMPONENTS = "components";


    /** @var array<string, BlockComponent> */
    private array $components = [];

    public function __construct(
        private string $condition
    ) {
    }

    public function getCondition(): string {
        return $this->condition;
    }

    public function setCondition(string $condition): self {
        $this->condition = $condition;
        return $this;
    }

    /** @return array<string, BlockComponent> */
    public function getComponents(): array {
        return $this->components;
    }

    public function addComponent(BlockComponent $component): self {
        $this->components[$component->getIdentifier()] = $component;
        return $this;
    }

    public function toNBT(): CompoundTag {
        $components = CompoundTag::create();
        foreach ($this->components as $id => $component) {
            $components->setTag($id, $component->toNBT());
        }

        return CompoundTag::create()
            ->setString(self::TAG_CONDITION, $this->condition)
            ->setTag(self::TAG_COMPONENTS, $components);
    }
}
