<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\Server;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsInfoSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("info", "Show world information");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $worldName = $context->getArguments()->string("world");
        $worldManager = Server::getInstance()->getWorldManager();
        $world = $worldManager->getWorldByName($worldName);

        $lines = [
            "§8World info: §f{$worldName}",
            " §7Generated: §a" . ($worldManager->isWorldGenerated($worldName) ? "yes" : "no"),
            " §7Loaded: " . ($world !== null ? "§ayes" : "§cno"),
            " §7Locked: " . (WorldUtils::isWorldLocked($worldName) ? "§cyes" : "§ano")
        ];

        if ($world !== null) {
            $spawn = $world->getSpawnLocation();
            $display = $world->getDisplayName();
            $folder = $world->getFolderName();

            $lines[] = " §7Display: §f{$display}";
            $lines[] = " §7Folder: §f{$folder}";
            $lines[] = " §7Players: §f" . count($world->getPlayers());
            $lines[] = " §7Entities: §f" . count($world->getEntities());
            $lines[] = " §7Chunks: §f" . count($world->getLoadedChunks());
            $lines[] = " §7Ticking chunks: §f" . count($world->getTickingChunks());
            $lines[] = " §7Tick time: §f" . round($world->getTickRateTime(), 2) . "ms";
            $lines[] = " §7Spawn: §f" . round($spawn->getX(), 2) . ", " . round($spawn->getY(), 2) . ", " . round($spawn->getZ(), 2);
        }

        $sender->sendMessage(implode("\n", $lines));
        return null;
    }
}
