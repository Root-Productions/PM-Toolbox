<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

class EnumArgument extends Argument {
    /** @var array<string, mixed> */
    private array $values = [];

    /**
     * @param array<int|string, mixed>|bool $values Use a list for string values, an associative map for parsed values, or bool when created by CommandArgument.
     */
    public function __construct(string $name, array|bool $values = [], mixed $optional = false, mixed $default = null) {
        if (is_bool($values)) {
            $default = $optional;
            $optional = $values;
            $values = [];
        }

        if (!is_bool($optional)) {
            $default = $optional;
            $optional = false;
        }

        parent::__construct($name, $optional, $default);
        $this->values = $this->normalizeValues($values);
        $this->commandParameter = CommandParameter::enum(
            $this->getName(),
            new CommandHardEnum($this->getName(), array_keys($this->values)),
            0,
            $this->isOptional()
        );
    }

    public function getTypeName(): string {
        return "enum";
    }

    public function getNetworkType(): int {
        return -1;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return array_key_exists(strtolower($test), $this->values);
    }

    public function parse(CommandSender $sender, string $arg): mixed {
        return $this->values[strtolower($arg)];
    }

    /** @return string[] */
    public function getEnumValues(): array {
        return array_keys($this->values);
    }

    private function normalizeValues(array $values): array {
        $normalized = [];

        foreach ($values as $key => $value) {
            $enumValue = is_int($key) ? (string) $value : (string) $key;
            $enumValue = trim($enumValue);
            if ($enumValue === "") {
                continue;
            }

            $normalized[strtolower($enumValue)] = is_int($key) ? $enumValue : $value;
        }

        return $normalized;
    }
}
