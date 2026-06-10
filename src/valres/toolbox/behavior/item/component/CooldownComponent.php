<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class CooldownComponent extends DataDrivenItemComponent {
    public const TYPE_USE = "use";
    public const TYPE_ATTACK = "attack";

    public function __construct(
        private readonly string $category,
        private readonly float $duration,
        private readonly ?string $type = null
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
