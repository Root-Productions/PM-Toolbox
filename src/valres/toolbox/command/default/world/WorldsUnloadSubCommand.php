<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\Server;
use pocketmine\world\WorldException;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\CommandInterceptor;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsUnloadSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("unload", "Unload a world");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $worldName = $context->getArguments()->string("world");
        $worldManager = Server::getInstance()->getWorldManager();

        try {
            if (WorldUtils::isWorldLocked($worldName)) {
                throw new WorldException("World '{$worldName}' is locked.");
            }

            $world = $worldManager->getWorldByName($worldName);
            if ($world === null) {
                throw new WorldException("World '{$worldName}' is already unloaded.");
            }

            $defaultWorld = WorldUtils::getDefaultWorldNonNull();
            if ($defaultWorld->getId() === $world->getId()) {
                throw new WorldException("Default world can't be unloaded.");
            }

            foreach ($world->getPlayers() as $player) {
                $player->teleport($defaultWorld->getSpawnLocation());
            }

            if (!$worldManager->unloadWorld($world, true)) {
                throw new WorldException("Unable to unload world '{$worldName}'.");
            }

            CommandInterceptor::updateCommand("worlds", new WorldsCommand());
            $sender->sendMessage("§aWorld '{$worldName}' has been unloaded.");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
