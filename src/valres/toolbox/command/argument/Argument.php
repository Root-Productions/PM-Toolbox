<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use valres\toolbox\command\exception\ArgumentException;
use valres\toolbox\ToolboxLoader;

abstract class Argument {
    protected CommandParameter $commandParameter;

    /** @throws ArgumentException */
    public function __construct(
        private string $name,
        private readonly bool $optional = false,
        private readonly mixed $default = null
    ) {
        if (str_contains($name, ' ')) {
            throw new ArgumentException("Argument 'name' cannot contain spaces: '{$name}'");
        }

        if (preg_match('/[A-Z]/', $name)) {
            ToolboxLoader::getLoader()->getLogger()->warning("Argument '{$name}' cannot contain upper-cases, changes to '" . strtolower($name) ."'");
            $this->name = strtolower($name);
        }

        $this->commandParameter = CommandParameter::standard(
            $this->name,
            $this->getNetworkType(),
            0,
            $this->optional
        );
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool {
        return $this->optional;
    }

    public function getDefault(): mixed {
        return $this->default;
    }

    public function hasDefault(): bool {
        return $this->default !== null;
    }

    /**
     * @return CommandParameter|null
     */
    public function getCommandParameter(): ?CommandParameter {
        return $this->commandParameter;
    }

    public function getMaxLength(): int {
        return 1;
    }

    public function getUsageFormatted(): string {
        $value = $this->name . ':' . $this->getTypeName();

        return $this->optional ? '[' . $value . ']' : '<' . $value . '>';
    }

    abstract public function getTypeName(): string;

    abstract public function getNetworkType(): int;

    abstract public function canParse(CommandSender $sender, string $test): bool;

    abstract public function parse(CommandSender $sender, string $arg): mixed;
}
