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
final class WorldsBackupSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("backup", "Backup a world");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new WorldArgument("world"));
        $this->addArgument(new StringArgument("name", true));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $args = $context->getArguments();

        $worldName = $args->string("world");
        $backupName = $args->string("name");

        try {
            $backupName ??= $worldName . "-" . date("Ymd-His");
            $copiedFiles = WorldUtils::backupWorld($worldName, $backupName);
            $sender->sendMessage("§aWorld '{$worldName}' has been backed up as '{$backupName}' ({$copiedFiles} file(s)).");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
