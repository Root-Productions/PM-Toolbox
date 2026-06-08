<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Server;
use valres\toolbox\command\enum\EnumList;
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
        self::refreshWorlds(false);

        return parent::getCommandParameter();
    }

    public function canParse(CommandSender $sender, string $test): bool {
        self::refreshWorlds(false);

        return parent::canParse($sender, $test);
    }

    public function parse(CommandSender $sender, string $arg): string {
        return strtolower($arg);
    }

    public static function refreshWorlds(bool $broadcast = true): void {
        EnumList::setEnumValues("world", self::getWorlds(), $broadcast);

        if ($broadcast) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->getNetworkSession()->syncAvailableCommands();
            }
        }
    }

    /** @return string[] */
    private static function getWorlds(): array {
        return WorldUtils::getAllWorlds();
    }
}
