<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use valres\toolbox\command\enum\EnumList;

class DynamicEnumArgument extends Argument {
    /**
     * @param string[]|bool $values Initial enum values, or bool when created by CommandArgument.
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
        EnumList::getOrCreate($this->getName(), $values);
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
        return EnumList::getEnumByName($this->getName())?->getValues() ?? [];
    }

    /** @param string[] $values */
    public function setValues(array $values, bool $broadcast = true): void {
        EnumList::setEnumValues($this->getName(), $values, $broadcast);
        $this->refreshCommandParameter();
    }

    private function refreshCommandParameter(): void {
        $this->commandParameter = CommandParameter::softEnum(
            $this->getName(),
            EnumList::getOrCreate($this->getName(), $this->getValues()),
            0,
            $this->isOptional()
        );
    }
}
