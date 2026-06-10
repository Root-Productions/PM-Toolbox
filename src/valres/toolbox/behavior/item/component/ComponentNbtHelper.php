<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

final class ComponentNbtHelper {
    public static function compound(array $values): CompoundTag {
        $tag = CompoundTag::create();
        foreach ($values as $name => $value) {
            if ($value === null) {
                continue;
            }

            $tag->setTag((string) $name, self::tag($value));
        }

        return $tag;
    }

    public static function stringList(array $values): ListTag {
        return new ListTag(array_map(
            static fn(string $value): StringTag => new StringTag($value),
            array_values($values)
        ), NBT::TAG_String);
    }

    public static function compoundList(array $values): ListTag {
        return new ListTag(array_map(
            static fn(array|CompoundTag $value): CompoundTag => $value instanceof CompoundTag ? $value : self::compound($value),
            array_values($values)
        ), NBT::TAG_Compound);
    }

    public static function tag(mixed $value): Tag {
        if ($value instanceof Tag) {
            return $value;
        }

        return match (true) {
            is_bool($value) => new ByteTag($value ? 1 : 0),
            is_int($value) => new IntTag($value),
            is_float($value) => new FloatTag($value),
            is_string($value) => new StringTag($value),
            is_array($value) => self::arrayTag($value),
            default => new StringTag((string) $value)
        };
    }

    private static function arrayTag(array $value): Tag {
        if (!array_is_list($value)) {
            return self::compound($value);
        }

        $values = array_values($value);
        if ($values === []) {
            return new ListTag([], NBT::TAG_End);
        }

        $first = $values[0];
        if (is_string($first)) {
            return self::stringList($values);
        }

        return self::compoundList($values);
    }
}
