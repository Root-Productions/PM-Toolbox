<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component;

use pocketmine\nbt\tag\Tag;

/** Defines the particles emitted when the block is destroyed. */
final class DestructionParticlesComponent extends BlockComponent {
    public function __construct(
        private readonly ?string $texture = null,
        private readonly ?string $tintMethod = null,
        private readonly ?int $particleCount = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:destruction_particles";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "texture" => $this->texture,
            "tint_method" => $this->tintMethod,
            "particle_count" => $this->particleCount
        ]);
    }
}
