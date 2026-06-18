<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;

/** Proxies the shared component value serializers for block components. */
final class ComponentNbtHelper {
    /**
     * @internal Low-level serialization helper for block components.
     *
     * @param array<string, mixed> $values
     */
    public static function compound(array $values): CompoundTag {
        return \valres\toolbox\behavior\item\component\ComponentNbtHelper::compound($values);
    }

    /**
     * @internal Low-level serialization helper for block components.
     *
     * @param array<int, array<string, mixed>|CompoundTag> $values
     */
    public static function compoundList(array $values): ListTag {
        return \valres\toolbox\behavior\item\component\ComponentNbtHelper::compoundList($values);
    }

    /** @internal Low-level serialization helper for block components. */
    public static function tag(mixed $value): Tag {
        return \valres\toolbox\behavior\item\component\ComponentNbtHelper::tag($value);
    }
}
