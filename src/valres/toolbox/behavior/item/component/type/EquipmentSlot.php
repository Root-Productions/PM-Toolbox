<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

enum EquipmentSlot: string {
    case ARMOR_HEAD = "slot.armor.head";
    case ARMOR_CHEST = "slot.armor.chest";
    case ARMOR_LEGS = "slot.armor.legs";
    case ARMOR_FEET = "slot.armor.feet";
    case WEAPON_OFF_HAND = "slot.weapon.offhand";
}
