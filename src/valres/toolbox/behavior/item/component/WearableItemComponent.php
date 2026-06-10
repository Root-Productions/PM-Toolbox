<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

class WearableItemComponent extends DataDrivenItemComponent {
    const ARMOR_CHEST = "slot.armor.chest";
    const ARMOR_FEET = "slot.armor.feet";
    const ARMOR_HEAD = "slot.armor.head";
    const ARMOR_LEGS = "slot.armor.legs";

    const WEAPON_OFF_HAND = "slot.weapon.offhand";

    public function __construct(
        private readonly string $slot,
        private readonly ?int $protection = null,
        private readonly ?bool $hidesPlayerLocation = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:wearable";
    }

    public function toNBT(): Tag {
        $NBT = CompoundTag::create()->setTag("slot", new StringTag($this->slot));

        if (isset($this->protection)) {
            $NBT->setTag("protection", new IntTag($this->protection));
        }

        if (isset($this->hidesPlayerLocation)) {
            $NBT->setTag("hides_player_location", new ByteTag($this->hidesPlayerLocation ? 1 : 0));
        }

        return $NBT;
    }
}