<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\attribute;

use Attribute;
use pocketmine\inventory\CreativeCategory;
use valres\toolbox\behavior\creative\CreativeGroupEnum;

#[Attribute(Attribute::TARGET_CLASS)]
class CreativeInventoryInfo {
    public function __construct(
        private readonly ?CreativeCategory $category = CreativeCategory::ITEMS,
        private readonly CreativeGroupEnum|string|null $group = null,
        private readonly bool $isHidden = false,
    ) {
    }

    public function getCategory(): ?CreativeCategory {
        return $this->category;
    }

    public function getGroup(): CreativeGroupEnum|string|null {
        return $this->group;
    }

    public function isHidden(): bool {
        return $this->isHidden;
    }
}
