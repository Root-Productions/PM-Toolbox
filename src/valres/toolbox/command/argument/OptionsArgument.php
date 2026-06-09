<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use valres\toolbox\command\exception\ArgumentException;

class OptionsArgument extends EnumArgument {
    /** @var array<string, mixed> */
    private array $enumValues = [];

    /** @var string[] */
    private array $enumNames = [];

    /** @throws ArgumentException */
    public function __construct(string $name, array $enumValues, bool $optional = false, mixed $default = null) {
        foreach ($enumValues as $key => $value) {
            $enumName = is_int($key) ? (string) $value : (string) $key;
            if ($enumName === "") {
                throw new ArgumentException("Option argument '{$name}' cannot contain an empty enum value.");
            }

            $this->enumNames[] = $enumName;
            $this->enumValues[strtolower($enumName)] = $value;
        }

        parent::__construct($name, $optional, $default);
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
        return $this->enumNames;
    }
}
