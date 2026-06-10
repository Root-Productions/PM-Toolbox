<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\item;

use pocketmine\nbt\tag\Tag;

interface ItemNbtSerializable {
    public static function identifier(): string;
    public function toNBT(): Tag;
}