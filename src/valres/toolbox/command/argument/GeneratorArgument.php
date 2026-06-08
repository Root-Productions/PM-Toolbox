<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\world\generator\GeneratorManager;
use valres\toolbox\command\exception\ArgumentException;

class GeneratorArgument extends DynamicEnumArgument {
    /** @throws ArgumentException */
    public function __construct(string $name = "generator", bool $optional = false) {
        parent::__construct($name, self::getGenerators(), $optional);
    }

    public function getTypeName(): string {
        return "generator";
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
        $this->setValues(self::getGenerators(), false);
    }

    /** @return string[] */
    private static function getGenerators(): array {
        return GeneratorManager::getInstance()->getGeneratorList();
    }
}
