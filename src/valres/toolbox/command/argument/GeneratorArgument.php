<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\world\generator\GeneratorManager;

class GeneratorArgument extends DynamicEnumArgument {
    /** @var array<string, self> */
    private static array $instances = [];

    public function __construct(string $name = "generator", bool $optional = false) {
        parent::__construct($name, self::getGenerators(), $optional);
        self::$instances[spl_object_id($this)] = $this;
    }

    public function getTypeName(): string {
        return "generator";
    }

    public function getCommandParameter(): ?CommandParameter {
        $this->refresh(false);

        return parent::getCommandParameter();
    }

    public function canParse(CommandSender $sender, string $test): bool {
        $this->refresh(false);

        return parent::canParse($sender, $test);
    }

    public function parse(CommandSender $sender, string $arg): string {
        return strtolower($arg);
    }

    public static function refreshGenerators(bool $broadcast = true): void {
        foreach (self::$instances as $instance) {
            $instance->refresh($broadcast);
        }
    }

    private function refresh(bool $broadcast): void {
        $this->setValues(self::getGenerators(), $broadcast);
    }

    /** @return string[] */
    private static function getGenerators(): array {
        return GeneratorManager::getInstance()->getGeneratorList();
    }
}
