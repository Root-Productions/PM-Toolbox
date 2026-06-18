<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;
use valres\toolbox\behavior\block\component\type\CardinalDirection;
use valres\toolbox\behavior\block\component\type\ConnectionRuleMode;
use valres\toolbox\behavior\block\component\ComponentNbtHelper;

/** Defines which neighboring blocks and directions this block connects to. */
final class ConnectionRuleComponent extends BlockComponent {
    /** @param CardinalDirection[]|string[] $enabledDirections */
    public function __construct(
        private readonly ConnectionRuleMode|string|null $acceptsConnectionsFrom = null,
        private readonly array $enabledDirections = []
    ) {
    }

    public static function identifier(): string {
        return "minecraft:connection_rule";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "accepts_connections_from" => $this->acceptsConnectionsFrom instanceof ConnectionRuleMode ? $this->acceptsConnectionsFrom->value : $this->acceptsConnectionsFrom,
            "enabled_directions" => $this->enabledDirections === [] ? null : array_map(
                static fn(CardinalDirection|string $direction): string => $direction instanceof CardinalDirection ? $direction->value : $direction,
                $this->enabledDirections
            )
        ]);
    }
}
