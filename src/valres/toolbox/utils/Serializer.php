<?php

declare(strict_types=1);

namespace valres\toolbox\utils;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

class Serializer {
    public static function serialize(mixed $data): string {
        return serialize($data);
    }

    public static function unserialize(mixed $data): mixed {
        return unserialize($data);
    }
}