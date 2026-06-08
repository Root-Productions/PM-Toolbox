<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\World;
use pocketmine\world\WorldException;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\exception\WorldUtilsException;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldDeleteSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("delete", "Delete a world");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world", false));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $name = $context->getArguments()->string("world");

        try {
            $world = WorldUtils::getLoadedWorldByName($name);

            if ($world instanceof World && WorldUtils::getDefaultWorldNonNull()->getId() === $world->getId()) {
                throw new WorldException("Default world can't be deleted.");
            }

            WorldUtils::removeWorld($name);

            $sender->sendMessage("§aWorld {$name} has been deleted.");
        } catch (WorldException|WorldUtilsException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}