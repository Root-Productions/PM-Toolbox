<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\BlockComponent;

/** Enables the block event fired when a player places the block. */
class OnPlayerPlacingComponent extends BlockComponent {
    public function __construct() {
    }


    public static function identifier(): string {
        return "minecraft:on_player_placing";
    }

    public function toNBT(): Tag {
        return CompoundTag::create();
    }
}
