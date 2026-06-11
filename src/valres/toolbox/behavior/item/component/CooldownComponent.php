<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\type\CooldownType;

/** Defines the cooldown category and duration triggered by the item. */
final class CooldownComponent extends DataDrivenItemComponent {
    public const TYPE_USE = "use";
    public const TYPE_ATTACK = "attack";

    public function __construct(
        private readonly string $category,
        private readonly float $duration,
        private readonly CooldownType|string|null $type = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:cooldown";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "category" => $this->category,
            "duration" => $this->duration,
            "type" => $this->type
        ]);
    }
}
