<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\Tag;

final class ComponentNbtHelper {
    public static function compound(array $values): CompoundTag {
        return \valres\toolbox\behavior\item\component\ComponentNbtHelper::compound($values);
    }

    public static function compoundList(array $values): ListTag {
        return \valres\toolbox\behavior\item\component\ComponentNbtHelper::compoundList($values);
    }

    public static function tag(mixed $value): Tag {
        return \valres\toolbox\behavior\item\component\ComponentNbtHelper::tag($value);
    }
}
