<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use valres\toolbox\command\exception\ArgumentException;
use valres\toolbox\utils\WorldUtils;

class WorldArgument extends DynamicEnumArgument {
    /** @throws ArgumentException */
    public function __construct(string $name = "world", bool $optional = false) {
        parent::__construct($name, self::getWorlds(), $optional);
    }

    public function getTypeName(): string {
        return "world";
    }

    public function getCommandParameter(): ?CommandParameter {
        $this->refreshGenerators();

        return parent::getCommandParameter();
    }

    public function canParse(CommandSender $sender, string $test): bool {
        $this->refreshGenerators();

        return parent::canParse($sender, $test);
    }

    public function parse(CommandSender $sender, string $arg): string {
        return strtolower($arg);
    }

    private function refreshGenerators(): void {
        $this->setValues(self::getWorlds(), false);
    }

    /** @return string[] */
    private static function getWorlds(): array {
        return WorldUtils::getAllWorlds();
    }
}
