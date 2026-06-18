<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Wraps an arbitrary component identifier and prebuilt NBT payload. */
final class RawBlockComponent extends BlockComponent {
    public function __construct(
        private readonly string $identifier,
        private readonly Tag $tag
    ) {
    }

    public static function identifier(): string {
        return "";
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function toNBT(): Tag {
        return $this->tag;
    }
}
