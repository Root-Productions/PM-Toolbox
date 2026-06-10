<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\exception\ItemRegistryException;

final class BundleInteractionComponent extends DataDrivenItemComponent {
    /** @throws ItemRegistryException */
    public function __construct(
        private readonly int $numViewableSlots,
    ) {
        if ($this->numViewableSlots <= 0 or $this->numViewableSlots > 64) {
            throw new ItemRegistryException("Component 'minecraft:bundle_interaction', value 'num_viewable_slots' must be between 1 and 64, got " . $this->numViewableSlots);
        }
    }

    public static function identifier(): string {
        return "minecraft:bundle_interaction";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["num_viewable_slots" => $this->numViewableSlots]);
    }
}
