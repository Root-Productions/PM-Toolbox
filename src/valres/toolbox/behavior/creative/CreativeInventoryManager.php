<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\creative;

use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\utils\SingletonTrait;
use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeGroup as PMMPCreativeGroup;
use pocketmine\lang\Translatable;
use ReflectionClass;
use valres\toolbox\behavior\attribute\CreativeInventoryInfo;

final class CreativeInventoryManager {
    use SingletonTrait;

    /** @var array<string, PMMPCreativeGroup> */
    private array $groups = [];

    /** @var array<string, CreativeCategory> */
    private array $groupToCategory = [];

    /** @var array<string, array<string, PMMPCreativeGroup>> */
    private array $categoryToGroups = [];

    public function __construct() {
        $this->loadCreativeGroups();
    }

    private function loadCreativeGroups(): void {
        if ($this->groups !== []) {
            return;
        }

        foreach (CreativeInventory::getInstance()->getAllEntries() as $entry) {
            $category = $entry->getCategory();
            $group = $entry->getGroup();
            if ($group === null) {
                continue;
            }

            $groupName = $this->getGroupName($group);
            $this->groups[$groupName] = $group;
            $this->categoryToGroups[$category->name][$groupName] = $group;
            $this->groupToCategory[$groupName] = $category;
        }
    }

    public function addToCreative(Item $item): void {
        $info = $this->readCreativeInfo($item);
        if ($info === null || $info->isHidden()) {
            return;
        }

        $this->add($item, $info->getCategory() ?? CreativeCategory::ITEMS, $info->getGroup());
    }

    public function add(Item $item, CreativeCategory $category = CreativeCategory::ITEMS, CreativeGroupEnum|string|null $groupName = null): void {
        $inventory = CreativeInventory::getInstance();
        if ($inventory->contains($item)) {
            $inventory->remove($item);
        }

        $inventory->add($item, $category, $this->resolveGroup($groupName, $category, $item));
    }

    public function getGroup(CreativeGroupEnum|string|null $groupName): ?PMMPCreativeGroup {
        if ($groupName === null) {
            return null;
        }

        $this->loadCreativeGroups();
        $groupName = $this->normalizeGroupName($groupName);
        return $this->groups[$groupName] ?? null;
    }

    public function getGroupCategory(CreativeGroupEnum|string|null $groupName): ?CreativeCategory {
        if ($groupName === null) {
            return null;
        }

        $this->loadCreativeGroups();
        $groupName = $this->normalizeGroupName($groupName);
        return $this->groupToCategory[$groupName] ?? null;
    }

    private function resolveGroup(CreativeGroupEnum|string|null $groupName, CreativeCategory $category, Item $icon): ?PMMPCreativeGroup {
        if ($groupName === null) {
            return null;
        }

        $this->loadCreativeGroups();
        $groupName = $this->normalizeGroupName($groupName);
        $group = $this->categoryToGroups[$category->name][$groupName]
            ?? $this->groups[$groupName]
            ?? new PMMPCreativeGroup($groupName, $icon);

        $this->groups[$groupName] = $group;
        $this->categoryToGroups[$category->name][$groupName] = $group;
        $this->groupToCategory[$groupName] ??= $category;

        return $group;
    }

    private function readCreativeInfo(Item $item): ?CreativeInventoryInfo {
        $attributes = (new ReflectionClass($item))->getAttributes(CreativeInventoryInfo::class);
        return $attributes === [] ? null : $attributes[0]->newInstance();
    }

    private function getGroupName(PMMPCreativeGroup $group): string {
        $name = $group->getName();
        return $name instanceof Translatable ? $name->getText() : $name;
    }

    private function normalizeGroupName(CreativeGroupEnum|string $groupName): string {
        return $groupName instanceof CreativeGroupEnum ? $groupName->value : $groupName;
    }
}
