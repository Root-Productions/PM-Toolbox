<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

class BooleanArgument extends EnumArgument {
    public function __construct(string $name, bool $optional = false, mixed $default = null) {
        parent::__construct($name, [
            "true" => true,
            "false" => false,
            "yes" => true,
            "no" => false,
            "1" => true,
            "0" => false,
        ], $optional, $default);
    }

    public function getTypeName(): string {
        return "bool";
    }
}
