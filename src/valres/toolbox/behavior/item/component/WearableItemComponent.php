<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class WearableItemComponent extends DataDrivenItemComponent {
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
        return ComponentNbtHelper::compound([
            "slot" => $this->slot,
            "protection" => $this->protection,
            "hides_player_location" => $this->hidesPlayerLocation
        ]);
    }
}
