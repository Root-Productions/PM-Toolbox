<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\WorldException;
use valres\toolbox\command\argument\OptionsArgument;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\ArgumentException;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsLockSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("lock", "Lock or unlock a world");
    }

    /** @throws ArgumentException|CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
        $this->addArgument(new OptionsArgument("state", [
            "on", "true", "yes" => true,
            "off", "false", "no" => false
        ], true));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $args = $context->getArguments();
        $worldName = $args->string("world");
        $state = $args->get("state");

        try {
            if ($state === null) {
                $locked = WorldUtils::isWorldLocked($worldName);
                $sender->sendMessage("§7World '{$worldName}' lock: " . ($locked ? "§con" : "§aoff"));
                return null;
            }

            WorldUtils::setWorldLocked($worldName, (bool) $state);
            $sender->sendMessage("§aWorld '{$worldName}' lock has been set to " . ((bool) $state ? "on" : "off") . ".");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
