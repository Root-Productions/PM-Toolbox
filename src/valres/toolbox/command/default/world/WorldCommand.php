<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use valres\toolbox\command\attribute\CommandInfo;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\Command;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;

#[CommandInfo("worlds", "Manage worlds", [])] #[CommandPermission(isOp: true)]
class WorldCommand extends Command {
    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addSubCommand(new WorldCreateSubCommand());
        $this->addSubCommand(new WorldDeleteSubCommand());
    }

    protected function onRun(CommandContext $context): mixed {
        return null;
    }
}