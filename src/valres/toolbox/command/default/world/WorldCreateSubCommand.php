<?php

declare(strict_types=1);

namespace valres\toolbox\command\default\world;

use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\GeneratorManagerEntry;
use pocketmine\world\WorldCreationOptions;
use pocketmine\world\WorldException;
use valres\toolbox\command\argument\GeneratorArgument;
use valres\toolbox\command\argument\StringArgument;
use valres\toolbox\command\attribute\CommandPermission;
use valres\toolbox\command\CommandContext;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\SubCommand;
use valres\toolbox\ToolboxLoader;

#[CommandPermission(isOp: true)]
final class WorldCreateSubCommand extends SubCommand {
    public function __construct() {
        parent::__construct("create", "Create a world");
    }

    /** @throws CommandConfigurationException */
    protected function configure(): void {
        $this->addArgument(new StringArgument("name"));
        $this->addArgument(new GeneratorArgument("generator"));
    }

    protected function onRun(CommandContext $context): mixed {
        $sender = $context->getSender();
        $args = $context->getArguments();

        $name = $args->string("name");
        $generator = $args->string("generator");

        $worldManager = ToolboxLoader::getLoader()->getServer()->getWorldManager();

        try {
            if ($worldManager->isWorldGenerated($name)) {
                throw new WorldException("World '{$name}' already exists.");
            }

            $generatorEntry = GeneratorManager::getInstance()->getGenerator($generator);
            if (!$generatorEntry instanceof GeneratorManagerEntry) {
                throw new WorldException("Generator '{$generator}' is invalid.");
            }

            $created = $worldManager->generateWorld(
                $name,
                WorldCreationOptions::create()
                    ->setSeed(mt_rand())
                    ->setGeneratorClass($generatorEntry->getGeneratorClass())
            );

            if (!$created) {
                throw new WorldException("Unable to generate the world.");
            }

            $sender->sendMessage("§aWorld {$name} has been created.");
        } catch (WorldException $e) {
            $sender->sendMessage("§cWorld error: " . $e->getMessage());
        }

        return null;
    }
}