<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\traits;

use BackedEnum;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;
use valres\toolbox\behavior\block\property\BlockStateProperty;

abstract class BlockTrait {
    /** @return list<BackedEnum|string> */
    abstract protected function enabledStates(): array;

    /** @return array<string, mixed> */
    protected function values(): array {
        return [];
    }

    abstract public static function identifier(): string;

    public function getIdentifier(): string {
        return static::identifier();
    }

    /** @return list<BlockStateProperty> */
    public function getProvidedProperties(): array {
        return [];
    }

    public function toNBT(): CompoundTag {
        $tag = CompoundTag::create()
            ->setTag("name", new StringTag($this->getIdentifier()))
            ->setTag("enabled_states", $this->enabledStatesTag());

        foreach ($this->values() as $name => $value) {
            if ($value === null) {
                continue;
            }

            $tag->setTag($name, $value instanceof Tag ? $value : ComponentNbtHelper::tag($value));
        }

        return $tag;
    }

    private function enabledStatesTag(): CompoundTag {
        $tag = CompoundTag::create();
        foreach ($this->enabledStates() as $state) {
            $tag->setTag(self::stateName($state), new ByteTag(1));
        }

        return $tag;
    }

    protected static function stateName(BackedEnum|string $state): string {
        $name = $state instanceof BackedEnum ? (string) $state->value : $state;
        return str_starts_with($name, "minecraft:") ? substr($name, 10) : $name;
    }

    protected static function fullStateName(BackedEnum|string $state): string {
        $name = $state instanceof BackedEnum ? (string) $state->value : $state;
        return str_contains($name, ":") ? $name : "minecraft:" . $name;
    }
}
