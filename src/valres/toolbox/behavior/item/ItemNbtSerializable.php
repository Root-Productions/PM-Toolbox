<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use pocketmine\nbt\tag\Tag;

interface ItemNbtSerializable {
    /**
     * Returns the Bedrock component or property identifier used as the NBT key.
     *
     * @return string
     */
    public static function identifier(): string;

    /**
     * Converts this component or property into its Bedrock NBT payload.
     *
     * @return Tag
     */
    public function toNBT(): Tag;
}
