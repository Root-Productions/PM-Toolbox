<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\WorldException;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsLoadSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("load", "Load a world");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $worldName = $context->getArguments()->string("world");

        try {
            if (!WorldUtils::lazyLoadWorld($worldName)) {
                throw new WorldException("Unable to load world '{$worldName}'.");
            }

            $sender->sendMessage("§aWorld '{$worldName}' has been loaded.");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
