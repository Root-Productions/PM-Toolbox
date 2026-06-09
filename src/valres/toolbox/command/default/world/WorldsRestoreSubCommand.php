<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\WorldException;
use valres\toolbox\command\argument\StringArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsRestoreSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("restore", "Restore a world backup");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new StringArgument("backup"));
        $this->addArgument(new StringArgument("world", true));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $args = $context->getArguments();

        $backupName = $args->string("backup");
        $worldName = $args->string("world");

        try {
            $copiedFiles = WorldUtils::restoreWorld($backupName, $worldName);
            $sender->sendMessage("§aBackup '{$backupName}' has been restored" . ($worldName !== null ? " as '{$worldName}'" : "") . " ({$copiedFiles} file(s)).");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
