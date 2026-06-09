<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use valres\toolbox\command\attribute\CommandInfo;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\Command;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;

#[CommandInfo("worlds", "Manage worlds", [])] #[CommandPermission(isOp: true)]
class WorldsCommand extends Command {
    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addSubCommand(new WorldsListSubCommand());
        $this->addSubCommand(new WorldsInfoSubCommand());
        $this->addSubCommand(new WorldsCreateSubCommand());
        $this->addSubCommand(new WorldsLoadSubCommand());
        $this->addSubCommand(new WorldsUnloadSubCommand());
        $this->addSubCommand(new WorldsDeleteSubCommand());
        $this->addSubCommand(new WorldsDuplicateSubCommand());
        $this->addSubCommand(new WorldsRenameSubCommand());
        $this->addSubCommand(new WorldsTeleportSubCommand());
        $this->addSubCommand(new WorldsLockSubCommand());
        $this->addSubCommand(new WorldsBackupSubCommand());
        $this->addSubCommand(new WorldsRestoreSubCommand());
    }

    protected function onRun(CommandContext $context): mixed {
        return null;
    }
}
