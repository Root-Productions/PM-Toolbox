<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use valres\toolbox\command\exception\ArgumentException;
use valres\toolbox\utils\WorldUtils;

class WorldArgument extends DynamicEnumArgument {
    /** @var array<string, self> */
    private static array $instances = [];

    /** @throws ArgumentException */
    public function __construct(string $name = "world", bool $optional = false) {
        parent::__construct($name, self::getWorlds(), $optional);
        self::$instances[spl_object_id($this)] = $this;
    }

    public function getTypeName(): string {
        return "world";
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
        return $arg;
    }

    public static function refreshWorlds(bool $broadcast = true): void {
        $worlds = self::getWorlds();

        if (self::$instances === []) {
            \valres\toolbox\command\enum\EnumList::setEnumValues("world", $worlds, $broadcast);
            return;
        }

        foreach (self::$instances as $instance) {
            $instance->refresh($broadcast, $worlds);
        }
    }

    private function refresh(bool $broadcast, ?array $worlds = null): void {
        $this->setValues($worlds ?? self::getWorlds(), $broadcast);
    }

    /** @return string[] */
    private static function getWorlds(): array {
        return WorldUtils::getAllWorlds();
    }
}
