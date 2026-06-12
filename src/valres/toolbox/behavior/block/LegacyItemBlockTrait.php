<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use valres\toolbox\behavior\attribute\LegacyItem;

trait LegacyItemBlockTrait {
    public function asItem(): Item {
        return new #[LegacyItem] class(new ItemIdentifier(-$this->getTypeId()), $this->getName(), clone $this) extends Item {
            public function __construct(
                ItemIdentifier $identifier,
                string $name,
                private readonly Block $block
            ) {
                parent::__construct($identifier, $name);
            }

            public function getBlock(?int $clickedFace = null): Block {
                return clone $this->block;
            }
        };
    }
}
