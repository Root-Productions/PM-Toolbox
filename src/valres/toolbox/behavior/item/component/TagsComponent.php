<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines custom item tags used by other item behavior and Molang checks. */
final class TagsComponent extends DataDrivenItemComponent {
    /**
     * Creates an item tag component.
     *
     * @param  string[] $tags
     */
    public function __construct(private readonly array $tags) {
    }

    /**
     * Returns tags that must also be mirrored into PocketMine's internal tag map.
     *
     * @return string[]
     */
    public function getTags(): array {
        return $this->tags;
    }

    public static function identifier(): string {
        return "minecraft:tags";
    }

    /**
     * Builds the Bedrock NBT payload for custom item tags.
     *
     * @return Tag
     */
    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "tags" => array_values($this->tags)
        ]);
    }
}
