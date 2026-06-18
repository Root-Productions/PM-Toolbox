<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\ChestObstructionRule;

/** Controls when this block obstructs the opening of a chest below it. */
final class ChestObstructionComponent extends BlockComponent {
    public function __construct(private readonly ChestObstructionRule|string $obstructionRule = ChestObstructionRule::SHAPE) {
    }

    public static function identifier(): string {
        return "minecraft:chest_obstruction";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "obstruction_rule" => $this->obstructionRule instanceof ChestObstructionRule ? $this->obstructionRule->value : $this->obstructionRule
        ]);
    }
}
