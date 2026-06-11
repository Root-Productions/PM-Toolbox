<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

final class MapColorComponent extends BlockComponent {
    public function __construct(
        private readonly string|array $color,
        private readonly ?string $tintMethod = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:map_color";
    }

    public static function rgb(int $red, int $green, int $blue, ?string $tintMethod = null): self {
        return new self([$red, $green, $blue], $tintMethod);
    }

    public function toNBT(): Tag {
        if ($this->tintMethod === null) {
            return ComponentNbtHelper::tag($this->color);
        }

        return ComponentNbtHelper::compound([
            "color" => $this->color,
            "tint_method" => $this->tintMethod
        ]);
    }
}
