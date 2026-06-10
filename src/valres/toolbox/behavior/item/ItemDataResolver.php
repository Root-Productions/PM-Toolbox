<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use pocketmine\block\Air;
use pocketmine\entity\Consumable;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\Bucket;
use pocketmine\item\Durable;
use pocketmine\item\Dye;
use pocketmine\item\FishingRod;
use pocketmine\item\Food;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Potion;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use valres\toolbox\behavior\item\builder\DataDrivenItemBuilder;
use valres\toolbox\behavior\item\builder\ItemBuilder;
use valres\toolbox\behavior\item\component\BlockPlacerItemComponent;
use valres\toolbox\behavior\item\component\DisplayNameComponent;
use valres\toolbox\behavior\item\component\DurabilityComponent;
use valres\toolbox\behavior\item\component\DyeableComponent;
use valres\toolbox\behavior\item\component\EnchantableComponent;
use valres\toolbox\behavior\item\component\FireResistantComponent;
use valres\toolbox\behavior\item\component\FoodComponent;
use valres\toolbox\behavior\item\component\FuelComponent;
use valres\toolbox\behavior\item\component\WearableItemComponent;
use valres\toolbox\behavior\item\property\BlockProperty;
use valres\toolbox\behavior\item\property\CanDestroyInCreativeProperty;
use valres\toolbox\behavior\item\property\DamageProperty;
use valres\toolbox\behavior\item\property\HandEquippedProperty;
use valres\toolbox\behavior\item\property\IconProperty;
use valres\toolbox\behavior\item\property\LiquidClippedProperty;
use valres\toolbox\behavior\item\property\MaxStackSizeProperty;
use valres\toolbox\behavior\item\property\MiningSpeedProperty;
use valres\toolbox\behavior\item\property\ShouldDespawnProperty;
use valres\toolbox\behavior\item\property\StackedByDataProperty;
use valres\toolbox\behavior\item\property\UseAnimationProperty;
use valres\toolbox\behavior\item\property\UseDurationProperty;

class ItemDataResolver {
    public static function applyDefault(ItemBuilder $builder): void {
        if (!$builder instanceof DataDrivenItemBuilder) {
            return;
        }

        self::applyDataDrivenProperties($builder);
        self::applyDataDrivenComponents($builder);
    }

    private static function applyDataDrivenProperties(DataDrivenItemBuilder $builder): void {
        $item = $builder->getItem();
        $icon = self::runtimePath($builder->getRuntimeId());

        $builder->addProperty(new IconProperty($icon));
        $builder->addProperty(new StackedByDataProperty(false));
        $builder->addProperty(new ShouldDespawnProperty(false));
        $builder->addProperty(new MaxStackSizeProperty($item->getMaxStackSize()));
        $builder->addProperty(new HandEquippedProperty($item instanceof Tool));
        $builder->addProperty(new CanDestroyInCreativeProperty(!$item instanceof Sword));
        $builder->addProperty(new LiquidClippedProperty($item instanceof Bucket));

        if (method_exists($item, "getMiningEfficiency")) {
            $builder->addProperty(new MiningSpeedProperty((float) $item->getMiningEfficiency(false)));
        }

        if ($item->getAttackPoints() > 1) {
            $builder->addProperty(new DamageProperty($item->getAttackPoints() - 1));
        }

        if (method_exists($item, "getBlock")) {
            $block = $item->getBlock();
            if (!$block instanceof Air) {
                $builder->addProperty(BlockProperty::from($block));
            }
        }

        if ($item instanceof Food || $item instanceof Consumable) {
            $builder->addProperty(new UseDurationProperty(20));
        }

        $builder->addProperty(new UseAnimationProperty(self::detectUseAnimation($item)));
    }

    private static function applyDataDrivenComponents(DataDrivenItemBuilder $builder): void {
        $item = $builder->getItem();

        $builder->addComponent(new DisplayNameComponent("item." . $builder->getRuntimeId() . ".name"));

        if (method_exists($item, "getEnchantability")) {
            $builder->addComponent(new EnchantableComponent(self::detectEnchantSlot($item), $item->getEnchantability()));
        }

        if (method_exists($item, "isFireProof")) {
            $builder->addComponent(new FireResistantComponent($item->isFireProof()));
        }

        if (method_exists($item, "getFuelTime") && $item->getFuelTime() > 0) {
            $builder->addComponent(new FuelComponent($item->getFuelTime() / 20));
        }

        if ($item instanceof Durable && !$item->isUnbreakable()) {
            $builder->addComponent(new DurabilityComponent($item->getMaxDurability()));
        }

        if ($item instanceof Armor) {
            $builder->addComponent(new WearableItemComponent(
                self::detectArmorSlot($item->getArmorSlot()),
                $item->getDefensePoints()
            ));
        }

        if (method_exists($item, "getBlock")) {
            $block = $item->getBlock();
            if (!$block instanceof Air) {
                $builder->addComponent(BlockPlacerItemComponent::from($block));
            }
        }

        if ($item instanceof Dye) {
            $color = $item->getColor();
            $builder->addComponent(new DyeableComponent(sprintf("%02X%02X%02X%02X", $color->r, $color->g, $color->b, $color->a)));
        }

        if ($item instanceof Food) {
            $builder->addComponent(new FoodComponent(
                $item->getFoodRestore(),
                (float) $item->getSaturationRestore(),
                !$item->requiresHunger()
            ));
        }
    }

    private static function runtimePath(string $runtimeId): string {
        if (!str_contains($runtimeId, ":")) {
            return $runtimeId;
        }

        [, $path] = explode(":", $runtimeId, 2);
        return $path;
    }

    private static function detectUseAnimation(object $item): string {
        if ($item instanceof Potion) {
            return UseAnimationProperty::DRINK;
        }

        if ($item instanceof Food || $item instanceof Consumable) {
            return UseAnimationProperty::EAT;
        }

        if ($item instanceof Bow) {
            return UseAnimationProperty::BOW;
        }

        return UseAnimationProperty::NONE;
    }

    private static function detectEnchantSlot(object $item): string {
        return match (true) {
            $item instanceof Sword => "sword",
            $item instanceof Pickaxe => "pickaxe",
            $item instanceof Axe => "axe",
            $item instanceof Hoe => "hoe",
            $item instanceof Shovel => "shovel",
            $item instanceof Bow => "bow",
            $item instanceof FishingRod => "fishing_rod",
            $item instanceof Shears => "shears",
            $item instanceof Armor => match ($item->getArmorSlot()) {
                ArmorInventory::SLOT_HEAD => "armor_head",
                ArmorInventory::SLOT_CHEST => "armor_torso",
                ArmorInventory::SLOT_LEGS => "armor_legs",
                ArmorInventory::SLOT_FEET => "armor_feet",
                default => "all"
            },
            default => "all"
        };
    }

    private static function detectArmorSlot(int $slot): string {
        return match ($slot) {
            ArmorInventory::SLOT_HEAD => WearableItemComponent::ARMOR_HEAD,
            ArmorInventory::SLOT_CHEST => WearableItemComponent::ARMOR_CHEST,
            ArmorInventory::SLOT_LEGS => WearableItemComponent::ARMOR_LEGS,
            ArmorInventory::SLOT_FEET => WearableItemComponent::ARMOR_FEET,
            default => "slot.armor"
        };
    }
}
