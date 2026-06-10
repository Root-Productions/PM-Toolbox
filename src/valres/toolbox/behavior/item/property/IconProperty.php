<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the texture or texture set used for the item's UI icon. */
final class IconProperty extends DataDrivenItemProperty {
    public function __construct(private readonly string|array $textures) {
    }

    public static function identifier(): string {
        return "minecraft:icon";
    }

    public static function textures(string $default, ?string $dyed = null, ?string $iconTrim = null): array {
        return [
            "textures" => array_filter([
                "default" => $default,
                "dyed" => $dyed,
                "icon_trim" => $iconTrim
            ], static fn(mixed $value): bool => $value !== null)
        ];
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->textures);
    }
}
