<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item\component;

use pocketmine\nbt\tag\Tag;

/** Allows the item to throw the projectile configured by the projectile component. */
final class ThrowableComponent extends DataDrivenItemComponent {
    public function __construct(
        private readonly ?bool $doSwingAnimation = null,
        private readonly ?float $minDrawDuration = null,
        private readonly ?float $maxDrawDuration = null,
        private readonly ?float $launchPowerScale = null,
        private readonly ?float $maxLaunchPower = null,
        private readonly ?bool $scalePowerByDrawDuration = null
    ) {
    }

    public static function identifier(): string {
        return "minecraft:throwable";
    }

    public function toNBT(): Tag {
        return ComponentNbtHelper::compound([
            "do_swing_animation" => $this->doSwingAnimation,
            "min_draw_duration" => $this->minDrawDuration,
            "max_draw_duration" => $this->maxDrawDuration,
            "launch_power_scale" => $this->launchPowerScale,
            "max_launch_power" => $this->maxLaunchPower,
            "scale_power_by_draw_duration" => $this->scalePowerByDrawDuration
        ]);
    }
}
