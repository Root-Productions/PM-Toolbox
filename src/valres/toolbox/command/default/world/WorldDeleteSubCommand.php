<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\World;
use pocketmine\world\WorldException;
use Throwable;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\ToolboxLoader;
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
        $logger = ToolboxLoader::getLoader()->getLogger();

        try {
            $logger->info("[WorldDeleteSubCommand] Delete requested for world '{$name}' by {$sender->getName()}.");

            $world = ToolboxLoader::getLoader()->getServer()->getWorldManager()->getWorldByName($name);
            $logger->info("[WorldDeleteSubCommand] Loaded world lookup for '{$name}': " . ($world instanceof World ? "found" : "not found") . ".");

            if ($world instanceof World && WorldUtils::getDefaultWorldNonNull()->getId() === $world->getId()) {
                throw new WorldException("Default world can't be deleted.");
            }

            $removedFiles = WorldUtils::removeWorld($name);
            $logger->info("[WorldDeleteSubCommand] World '{$name}' deleted, removed {$removedFiles} entries.");

            $sender->sendMessage("§aWorld {$name} has been deleted.");
        } catch (WorldException|WorldUtilsException|Throwable $e) {
            $logger->error("[WorldDeleteSubCommand] Failed to delete world '{$name}': {$e->getMessage()}");
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
