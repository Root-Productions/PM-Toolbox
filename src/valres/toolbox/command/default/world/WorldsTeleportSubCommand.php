<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\entity\Entity;
use pocketmine\world\WorldException;
use valres\toolbox\command\argument\TargetArgument;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsTeleportSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("teleport", "Teleport to a world", ["tp"]);
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
        $this->addArgument(new TargetArgument("target", true));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $args = $context->getArguments();
        $worldName = $args->string("world");

        try {
            $world = WorldUtils::getLoadedWorldByName($worldName);
            if ($world === null) {
                throw new WorldException("Unable to load world '{$worldName}'.");
            }

            $targets = $args->resolveTargets(static fn(Entity $entity) => $entity->teleport($world->getSpawnLocation()));
            if ($targets === []) {
                throw new WorldException("No valid target found.");
            }

            $sender->sendMessage("§aTeleported " . count($targets) . " target(s) to '{$worldName}'.");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
