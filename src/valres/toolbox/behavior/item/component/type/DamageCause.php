<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component\type;

enum DamageCause: string {
    case ALL = "all";
    case ENTITY_ATTACK = "entity_attack";
    case ENTITY_EXPLOSION = "entity_explosion";
    case BLOCK_EXPLOSION = "block_explosion";
    case FIRE = "fire";
    case FIRE_TICK = "fire_tick";
    case LAVA = "lava";
    case MAGMA = "magma";
    case FALL = "fall";
    case PROJECTILE = "projectile";
    case MAGIC = "magic";
    case WITHER = "wither";
}
