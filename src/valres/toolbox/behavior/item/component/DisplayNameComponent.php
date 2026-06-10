<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class DisplayNameComponent extends DataDrivenItemComponent {
    public function __construct(private readonly string $value) {
    }

    public static function identifier(): string {
        return "minecraft:display_name";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["value" => $this->value]);
    }
}
