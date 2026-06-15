<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\creative;

enum CreativeGroupEnum: string {
    case ANVIL = "itemGroup.name.anvil";
    case ARROW = "itemGroup.name.arrow";
    case AXE = "itemGroup.name.axe";
    case BANNER = "itemGroup.name.banner";
    case BANNER_PATTERN = "itemGroup.name.banner_pattern";
    case BED = "itemGroup.name.bed";
    case BOAT = "itemGroup.name.boat";
    case BOOTS = "itemGroup.name.boots";
    case BUNDLE = "itemGroup.name.bundle";
    case BUTTONS = "itemGroup.name.buttons";
    case CANDLES = "itemGroup.name.candles";
    case CHALKBOARD = "itemGroup.name.chalkboard";
    case CHEST = "itemGroup.name.chest";
    case CHESTPLATE = "itemGroup.name.chestplate";
    case CONCRETE = "itemGroup.name.concrete";
    case CONCRETE_POWDER = "itemGroup.name.concretePowder";
    case COOKED_FOOD = "itemGroup.name.cookedFood";
    case COPPER = "itemGroup.name.copper";
    case CORAL = "itemGroup.name.coral";
    case CORAL_DECORATIONS = "itemGroup.name.coral_decorations";
    case CROP = "itemGroup.name.crop";
    case DOOR = "itemGroup.name.door";
    case DYE = "itemGroup.name.dye";
    case ENCHANTED_BOOK = "itemGroup.name.enchantedBook";
    case FENCE = "itemGroup.name.fence";
    case FENCE_GATE = "itemGroup.name.fenceGate";
    case FIREWORK = "itemGroup.name.firework";
    case FIREWORK_STARS = "itemGroup.name.fireworkStars";
    case FLOWER = "itemGroup.name.flower";
    case GLASS = "itemGroup.name.glass";
    case GLASS_PANE = "itemGroup.name.glassPane";
    case GLAZED_TERRACOTTA = "itemGroup.name.glazedTerracotta";
    case GOAT_HORN = "itemGroup.name.goatHorn";
    case GRASS = "itemGroup.name.grass";
    case HANGING_SIGN = "itemGroup.name.wallSign";
    case HELMET = "itemGroup.name.helmet";
    case HOE = "itemGroup.name.hoe";
    case HORSE_ARMOR = "itemGroup.name.horseArmor";
    case LEAVES = "itemGroup.name.leaves";
    case LEGGINGS = "itemGroup.name.leggings";
    case LINGERING_POTION = "itemGroup.name.lingeringPotion";
    case LOG = "itemGroup.name.log";
    case MINECART = "itemGroup.name.minecart";
    case MISC_FOOD = "itemGroup.name.miscFood";
    case MOB_EGGS = "itemGroup.name.mobEgg";
    case MONSTER_STONE_EGG = "itemGroup.name.monsterStoneEgg";
    case MUSHROOM = "itemGroup.name.mushroom";
    case NETHER_WART_BLOCK = "itemGroup.name.netherWartBlock";
    case ORE = "itemGroup.name.ore";
    case PERMISSION = "itemGroup.name.permission";
    case PICKAXE = "itemGroup.name.pickaxe";
    case PLANKS = "itemGroup.name.planks";
    case POTION = "itemGroup.name.potion";
    case PRESSURE_PLATE = "itemGroup.name.pressurePlate";
    case RAIL = "itemGroup.name.rail";
    case RAW_FOOD = "itemGroup.name.rawFood";
    case RECORD = "itemGroup.name.record";
    case SANDSTONE = "itemGroup.name.sandstone";
    case SAPLING = "itemGroup.name.sapling";
    case SEED = "itemGroup.name.seed";
    case SHOVEL = "itemGroup.name.shovel";
    case SHULKER_BOX = "itemGroup.name.shulkerBox";
    case SIGN = "itemGroup.name.sign";
    case SKULL = "itemGroup.name.skull";
    case SLAB = "itemGroup.name.slab";
    case SMITHING_TEMPLATES = "itemGroup.name.smithing_templates";
    case SPLASH_POTION = "itemGroup.name.splashPotion";
    case STAINED_CLAY = "itemGroup.name.stainedClay";
    case STAIRS = "itemGroup.name.stairs";
    case STONE = "itemGroup.name.stone";
    case STONE_BRICK = "itemGroup.name.stoneBrick";
    case SWORD = "itemGroup.name.sword";
    case TRAPDOOR = "itemGroup.name.trapdoor";
    case WALLS = "itemGroup.name.walls";
    case WOOD = "itemGroup.name.wood";
    case WOOL = "itemGroup.name.wool";
    case WOOL_CARPET = "itemGroup.name.woolCarpet";

    public function getName(): string {
        return $this->name;
    }

    public function getValue(): string {
        return $this->value;
    }
}
