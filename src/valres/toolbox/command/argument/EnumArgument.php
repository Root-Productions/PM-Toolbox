<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

abstract class EnumArgument extends Argument {
    /** @var array<string, mixed> */
    protected const VALUES = [];

    public function __construct(string $name, bool $optional = false, mixed $default = null) {
        parent::__construct($name, $optional, $default);

        $this->commandParameter = CommandParameter::enum(
            $name,
            new CommandHardEnum("", $this->getEnumValues()),
            0,
            $optional
        );
    }

    public function getNetworkType(): int {
        return -1;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return (bool) preg_match(
            "/^(" . implode("|", array_map(static fn(string $value): string => preg_quote(strtolower($value), "/"), $this->getEnumValues())) . ")$/iu",
            $test
        );
    }

    public function getValue(string $string): mixed {
        return static::VALUES[strtolower($string)] ?? null;
    }

    public function getEnumValues(): array {
        return array_values(array_map(static fn(string|int $value): string => (string) $value, array_keys(static::VALUES)));
    }
}
