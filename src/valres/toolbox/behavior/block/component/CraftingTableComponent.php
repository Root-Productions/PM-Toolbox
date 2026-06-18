<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

/** Makes the block expose a crafting table with the configured recipe tags. */
final class CraftingTableComponent extends BlockComponent {
    /** @param string[] $craftingTags */
    public function __construct(
        private readonly array $craftingTags,
        private readonly ?string $tableName = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:crafting_table";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "table_name" => $this->tableName,
            "crafting_tags" => $this->craftingTags
        ]);
    }
}
