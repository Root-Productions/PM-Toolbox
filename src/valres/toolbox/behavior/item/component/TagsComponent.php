<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Defines custom item tags used by other item behavior and Molang checks. */
final class TagsComponent extends DataDrivenItemComponent {
    /** @param string[] $tags */
    public function __construct(private readonly array $tags) {
    }

    public function getTags(): array {
        return $this->tags;
    }

    public static function identifier(): string {
        return "minecraft:tags";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "tags" => array_values($this->tags)
        ]);
    }
}
