<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

enum EnchantSlot: string {
    case ALL = "all";
    case ARMOR_FEET = "armor_feet";
    case ARMOR_TORSO = "armor_torso";
    case ARMOR_HEAD = "armor_head";
    case ARMOR_LEGS = "armor_legs";
    case AXE = "axe";
    case BOW = "bow";
    case CARROT_STICK = "carrot_stick";
    case COSMETIC_HEAD = "cosmetic_head";
    case CROSSBOW = "crossbow";
    case ELYTRA = "elytra";
    case FISHING_ROD = "fishing_rod";
    case FLINTSTEEL = "flintsteel";
    case GROUP_ARMOR = "g_armor";
    case GROUP_DIGGING = "g_digging";
    case GROUP_TOOL = "g_tool";
    case HOE = "hoe";
    case MELEE_SPEAR = "melee_spear";
    case NONE = "none";
    case PICKAXE = "pickaxe";
    case SHEARS = "shears";
    case SHIELD = "shield";
    case SHOVEL = "shovel";
    case SPEAR = "spear";
    case SWORD = "sword";
}
