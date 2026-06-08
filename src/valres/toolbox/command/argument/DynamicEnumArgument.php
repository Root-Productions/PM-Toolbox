<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use valres\toolbox\command\enum\EnumList;

class DynamicEnumArgument extends Argument {
    /** @var string[] */
    private array $values;

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
        EnumList::setEnumValues($this->getName(), $this->values, true);
        $this->refreshCommandParameter();
    }

    public function getTypeName(): string {
        return "enum";
    }

    public function getNetworkType(): int {
        return -1;
    }

    public function getCommandParameter(): ?CommandParameter {
        $this->refreshCommandParameter();

        return parent::getCommandParameter();
    }

    public function canParse(CommandSender $sender, string $test): bool {
        return in_array(strtolower($test), array_map('strtolower', $this->getValues()), true);
    }

    public function parse(CommandSender $sender, string $arg): string {
        return $arg;
    }

    /** @return string[] */
    public function getValues(): array {
        return $this->values;
    }

    /** @param string[] $values */
    public function setValues(array $values, bool $broadcast = true): void {
        $this->values = $this->normalizeValues($values);
        EnumList::setEnumValues($this->getName(), $this->values, $broadcast);
        $this->refreshCommandParameter();
    }

    public function addValue(string $value, bool $broadcast = true): void {
        $values = $this->values;
        $values[] = $value;
        $this->setValues($values, $broadcast);
    }

    public function removeValue(string $value, bool $broadcast = true): void {
        $value = strtolower($value);
        $this->setValues(array_values(array_filter(
            $this->values,
            static fn(string $current): bool => strtolower($current) !== $value
        )), $broadcast);
    }

    protected function refreshCommandParameter(): void {
        $this->commandParameter = CommandParameter::softEnum(
            $this->getName(),
            EnumList::getOrCreate($this->getName(), $this->values),
            0,
            $this->isOptional()
        );
    }

    /** @return string[] */
    private function normalizeValues(array $values): array {
        $normalized = [];
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== "") {
                $normalized[strtolower($value)] = $value;
            }
        }

        return array_values($normalized);
    }
}
