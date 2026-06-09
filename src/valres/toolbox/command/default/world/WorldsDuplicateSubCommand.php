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
use valres\toolbox\ToolboxLoader;
use valres\toolbox\utils\WorldUtils;

#[CommandPermission(isOp: true)]
final class WorldsDuplicateSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("duplicate", "Duplicate a world");
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
        $duplicateName = $args->string("name");

        $worldManager = ToolboxLoader::getLoader()->getServer()->getWorldManager();

        try {
            if ($worldManager->isWorldGenerated($duplicateName)) {
                throw new WorldException("World '{$duplicateName}' already exists.");
            }

            if (!$worldManager->isWorldGenerated($worldName)) {
                throw new WorldException("World '{$worldName}' doesn't exist.");
            }

            $copiedFiles = WorldUtils::duplicateWorld($worldName, $duplicateName);
            $sender->sendMessage("§aWorld '{$worldName}' has been duplicated as '{$duplicateName}' ({$copiedFiles} file(s)).");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}
