<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\property;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\item\component\ComponentNbtHelper;

/** Defines the base mining speed used by older item formats. */
final class MiningSpeedProperty extends DataDrivenItemProperty {
    public function __construct(private readonly float $value) {
    }

    public static function identifier(): string {
        return "mining_speed";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::tag($this->value);
    }
}
