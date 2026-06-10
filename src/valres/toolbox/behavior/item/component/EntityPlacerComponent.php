<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

final class EntityPlacerComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly string $entity,
        private readonly ?array $dispenseOn = null,
        private readonly ?array $useOn = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:entity_placer";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "entity" => $this->entity,
            "dispense_on" => $this->dispenseOn,
            "use_on" => $this->useOn
        ]);
    }
}
