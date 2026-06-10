<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class FireResistantComponent extends DataDrivenItemComponent {
    public function __construct(private readonly bool $value = true) {
    }

    public static function identifier(): string {
        return "minecraft:fire_resistant";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound(["value" => $this->value]);
    }
}
