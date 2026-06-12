<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use BackedEnum;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\ItemComponentValue;

/** Converts component scalar and array values into NBT tags. */
final class ComponentNbtHelper {
    /**
     * Converts an associative array into a CompoundTag and skips null values.
     *
     * @internal Low-level serialization helper for item components and properties.
     *
     * @param  array<string, mixed> $values
     *
     * @return CompoundTag
     */
    public static function compound(array $values): CompoundTag {
        $tag = CompoundTag::create();
        foreach ($values as $name => $value) {
            $value = self::normalizeValue($value);
            if ($value === null) {
                continue;
            }

            $tag->setTag((string) $name, self::tag($value));
        }

        return $tag;
    }

    /**
     * Converts a string array into a typed NBT list.
     *
     * @internal Low-level serialization helper for item components and properties.
     *
     * @param  string[] $values
     *
     * @return ListTag
     */
    public static function stringList(array $values): ListTag {
        return new ListTag(array_map(
            static fn(string $value): StringTag => new StringTag($value),
            array_values($values)
        ), NBT::TAG_String);
    }

    /**
     * Converts a numeric array into a typed NBT list.
     *
     * @internal Low-level serialization helper for vector-like component payloads.
     *
     * @param  int[]|float[] $values
     *
     * @return ListTag
     */
    public static function numericList(array $values): ListTag {
        $values = array_values($values);
        $containsFloat = false;
        foreach ($values as $value) {
            if (is_float($value)) {
                $containsFloat = true;
                break;
            }
        }

        return new ListTag(array_map(
            static fn(int|float $value): IntTag|FloatTag => $containsFloat ? new FloatTag((float) $value) : new IntTag($value),
            $values
        ), $containsFloat ? NBT::TAG_Float : NBT::TAG_Int);
    }

    /**
     * Converts an array of associative arrays or CompoundTags into a compound NBT list.
     *
     * @internal Low-level serialization helper for item components and properties.
     *
     * @param  array<int, array<string, mixed>|CompoundTag|ItemComponentValue> $values
     *
     * @return ListTag
     */
    public static function compoundList(array $values): ListTag {
        return new ListTag(array_map(
            static function (array|CompoundTag|ItemComponentValue $value): CompoundTag {
                if ($value instanceof CompoundTag) {
                    return $value;
                }

                if ($value instanceof ItemComponentValue) {
                    return self::compound($value->toArray());
                }

                return self::compound($value);
            },
            array_values($values)
        ), NBT::TAG_Compound);
    }

    /**
     * Converts a scalar, array or existing tag into the matching NBT tag type.
     *
     * @internal Low-level serialization helper for item components and properties.
     *
     * @param  mixed $value
     *
     * @return Tag
     */
    public static function tag(mixed $value): Tag {
        $value = self::normalizeValue($value);

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

    /**
     * Converts an array into either a CompoundTag or a typed ListTag.
     *
     * @internal Low-level serialization helper for item components and properties.
     *
     * @param  array<int|string, mixed> $value
     *
     * @return Tag
     */
    private static function arrayTag(array $value): Tag {
        $value = self::normalizeValue($value);

        if (!array_is_list($value)) {
            return self::compound($value);
        }

        $values = array_values($value);
        if ($values === []) {
            return new ListTag([], NBT::TAG_End);
        }

        $first = $values[0];
        if (is_string($first) && self::all($values, "is_string")) {
            return self::stringList($values);
        }

        if ((is_int($first) || is_float($first)) && self::all($values, static fn(mixed $value): bool => is_int($value) || is_float($value))) {
            return self::numericList($values);
        }

        if (is_bool($first) && self::all($values, "is_bool")) {
            return new ListTag(array_map(
                static fn(bool $value): ByteTag => new ByteTag($value ? 1 : 0),
                $values
            ), NBT::TAG_Byte);
        }

        return self::compoundList($values);
    }

    private static function all(array $values, callable $predicate): bool {
        foreach ($values as $value) {
            if (!$predicate($value)) {
                return false;
            }
        }

        return true;
    }

    private static function normalizeValue(mixed $value): mixed {
        if ($value instanceof ItemComponentValue) {
            return $value->toArray();
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_array($value)) {
            return array_map(static fn(mixed $entry): mixed => self::normalizeValue($entry), $value);
        }

        return $value;
    }
}
