<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use InvalidArgumentException;
use pocketmine\item\Item;
use ReflectionClass;
use ReflectionException;
use valres\toolbox\behavior\attribute\DataDrivenItem;
use valres\toolbox\behavior\attribute\LegacyItem;

enum ItemFormatEnum: int {
    case LEGACY = 0;
    case DATA_DRIVEN = 1;

    /**
     * Resolves an item format from its class attributes.
     *
     * @param  Item $item
     *
     * @throws ReflectionException|InvalidArgumentException
     * @return self
     */
    public static function fromItem(Item $item): self {
        return self::fromClass($item::class);
    }

    /**
     * Resolves an item format from DataDrivenItem or LegacyItem attributes.
     *
     * @param class-string<Item> $itemClass
     *
     * @throws ReflectionException|InvalidArgumentException
     * @return self
     */
    public static function fromClass(string $itemClass): self {
        $reflection = new ReflectionClass($itemClass);

        $hasDataDriven = $reflection->getAttributes(DataDrivenItem::class) !== [];
        $hasLegacy = $reflection->getAttributes(LegacyItem::class) !== [];

        if ($hasDataDriven && $hasLegacy) {
            throw new InvalidArgumentException("Item '{$itemClass}' cannot use both DataDrivenItem and LegacyItem attributes.");
        }

        if ($hasDataDriven) {
            return self::DATA_DRIVEN;
        }

        if ($hasLegacy) {
            return self::LEGACY;
        }

        throw new InvalidArgumentException("Item '{$itemClass}' must use either DataDrivenItem or LegacyItem attribute.");
    }
}
