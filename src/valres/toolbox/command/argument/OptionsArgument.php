<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use valres\toolbox\command\exception\ArgumentException;

class OptionsArgument extends EnumArgument {
    /** @throws ArgumentException */
    public function __construct(string $name, private readonly array $enumValues, bool $optional = false, mixed $default = null) {
        parent::__construct($name, $enumValues, $optional, $default);
    }

    public function getTypeName(): string {
        return "string";
    }

    public function getEnumName(): string {
        return "option";
    }

    public function parse(CommandSender $sender, string $arg): mixed {
        $key = strtolower($arg);

        return $this->enumValues[$key] ?? $arg;
    }

    public function getEnumValues(): array {
        return array_keys($this->enumValues);
    }
}
