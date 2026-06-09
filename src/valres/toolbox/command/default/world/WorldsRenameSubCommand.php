<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\WorldException;
use valres\toolbox\command\argument\StringArgument;
use valres\toolbox\command\argument\WorldArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsRenameSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("rename", "Rename a world");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
        $this->addArgument(new StringArgument("name"));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $args = $context->getArguments();

        $worldName = $args->string("world");
        $newName = $args->string("name");

        try {
            WorldUtils::renameWorld($worldName, $newName);
            $sender->sendMessage("§aWorld '{$worldName}' has been renamed to '{$newName}'.");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
