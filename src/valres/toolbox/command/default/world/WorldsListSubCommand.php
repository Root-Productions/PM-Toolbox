<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\Server;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsListSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("list", "List worlds");
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $worldManager = Server::getInstance()->getWorldManager();

        $loadedWorlds = [];
        $unloadedWorlds = [];

        foreach (WorldUtils::getAllWorlds() as $fileName) {
            if ($worldManager->isWorldLoaded($fileName)) {
                $world = $worldManager->getWorldByName($fileName);
                if ($world === null) {
                    continue;
                }

                $loadedWorlds[] = [
                    "name" => $fileName,
                    "display" => $world->getDisplayName(),
                    "players" => count($world->getPlayers()),
                    "entities" => count($world->getEntities()),
                    "chunks" => count($world->getLoadedChunks()),
                    "tickingChunks" => count($world->getTickingChunks()),
                    "tickTime" => $world->getTickRateTime(),
                    "locked" => WorldUtils::isWorldLocked($fileName),
                    "loaded" => true
                ];
            } else {
                $unloadedWorlds[] = [
                    "name" => $fileName,
                    "locked" => WorldUtils::isWorldLocked($fileName),
                    "loaded" => false
                ];
            }
        }

        $worlds = array_merge($loadedWorlds, $unloadedWorlds);
        $lines = [];

        foreach ($worlds as $data) {
            if ($data["loaded"]) {
                $worldName = $data["name"] !== $data["display"]
                    ? "{$data["name"]} §8(§7{$data["display"]}§8)"
                    : $data["name"];

                $tickColor = match (true) {
                    $data["tickTime"] <= 25 => "§a",
                    $data["tickTime"] <= 40 => "§7",
                    default => "§c"
                };

                $lines[] =
                    " §a| §f{$worldName}" .
                    " §8(§7players: §f{$data["players"]}," .
                    " §7entities: §f{$data["entities"]}," .
                    " §7chunks: §f{$data["chunks"]}," .
                    " §7ticking: §f{$data["tickingChunks"]}," .
                    " §7time: {$tickColor}" . round($data["tickTime"], 2) . "ms," .
                    " §7locked: " . ($data["locked"] ? "§cyes" : "§ano") . "§8)";
            } else {
                $lines[] = " §c| §7{$data["name"]} §8(§cnot loaded§8, §70 players, §7locked: " . ($data["locked"] ? "§cyes" : "§ano") . "§8)";
            }
        }

        $sender->sendMessage("§8Worlds §7(" . count($worlds) . "):\n" . implode("\n", $lines));
        return null;
    }
}
