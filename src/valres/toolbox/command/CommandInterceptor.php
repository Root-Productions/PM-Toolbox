<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use pocketmine\command\Command as PMCommand;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketAssembler;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketDisassembler;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use pocketmine\Server;
use valres\toolbox\command\argument\Argument;
use valres\toolbox\command\enum\EnumList;
use valres\toolbox\packet\PacketHandlerInterface;
use valres\toolbox\ToolboxLoader;

class CommandInterceptor implements PacketHandlerInterface {
    private static bool $isIntercepting = false;

    public function getPacketIds(): array {
        return [AvailableCommandsPacket::class];
    }

    public function handle(Packet $packet, NetworkSession $session): bool {
        if (!$packet instanceof AvailableCommandsPacket) {
            return true;
        }

        if (self::$isIntercepting) {
            return true;
        }

        $player = $session->getPlayer();
        if (!$player instanceof Player) {
            return true;
        }

        $disassembled = AvailableCommandsPacketDisassembler::disassemble($packet);
        $commandDataList = $disassembled->commandData;
        $filtered = [];

        $server = Server::getInstance();
        foreach ($commandDataList as $commandData) {
            $commandName = $commandData->getName();
            $command = $server->getCommandMap()->getCommand($commandName);
            $keep = true;

            if ($command instanceof Command) {
                foreach ($command->getRules() as $rule) {
                    if (!$rule->canSee($player)) {
                        $keep = false;
                        break;
                    }
                }

                if ($keep) {
                    $commandData->overloads = $this->getOverloads($player, $command);
                }
            }

            if ($keep) {
                $filtered[] = $commandData;
            }
        }

        self::$isIntercepting = true;
        try {
            $session->sendDataPacket(AvailableCommandsPacketAssembler::assemble(
                $filtered,
                [],
                array_values(EnumList::getEnums())
            ));
        } finally {
            self::$isIntercepting = false;
        }

        return false;
    }

    /**
     * Builds the network overloads from the current command tree.
     *
     * getOverloads is the correct approach here because it reads the actual
     * Command/SubCommand/Argument objects. The old generateOverloads variant
     * depended on a previous API and could drift from runtime behavior.
     *
     * @return CommandOverload[]
     */
    private function getOverloads(CommandSender $sender, Command|SubCommand $command): array {
        $overloads = [];

        foreach ($command->getSubCommands() as $sub) {
            if (!$this->canSee($sender, $sub)) {
                continue;
            }

            $param = $this->createSubCommandParameter($sub);

            $child = $this->getOverloads($sender, $sub);
            if (!empty($child)) {
                foreach ($child as $ov) {
                    $overloads[] = new CommandOverload(false, [clone $param, ...$ov->getParameters()]);
                }
            } else {
                $overloads[] = new CommandOverload(false, [$param]);
            }
        }

        foreach ($this->getArgumentOverloadList($command) as $overload) {
            $overloads[] = $overload;
        }

        return $overloads;
    }

    /**
     * @return CommandOverload[]
     */
    private function getArgumentOverloadList(Command|SubCommand $command): array {
        $sets = $this->normalizeArgumentSets($command->getArguments());
        if ($sets === []) {
            return [new CommandOverload(false, [])];
        }

        $overloads = [];
        $indexes = array_fill(0, count($sets), 0);
        $total = array_product(array_map(fn($s) => count($s), $sets));

        for ($i = 0; $i < $total; ++$i) {
            $params = [];
            foreach ($indexes as $k => $idx) {
                $argument = $sets[$k][$idx];
                $param = clone $argument->getCommandParameter();
                $this->refreshHardEnumName($param);
                $params[] = $param;
            }
            $overloads[] = new CommandOverload(false, $params);

            for ($j = count($indexes) - 1; $j >= 0; --$j) {
                $indexes[$j]++;
                if ($indexes[$j] < count($sets[$j])) break;
                $indexes[$j] = 0;
            }
        }

        return $overloads;
    }

    /**
     * @param array<int, Argument|array<int, Argument>> $arguments
     * @return array<int, array<int, Argument>>
     */
    private function normalizeArgumentSets(array $arguments): array {
        $sets = [];

        foreach ($arguments as $argument) {
            $set = is_array($argument) ? $argument : [$argument];
            if ($set === []) {
                continue;
            }

            $sets[] = $set;
        }

        return $sets;
    }

    private function canSee(CommandSender $sender, Command|SubCommand $command): bool {
        foreach ($command->getRules() as $rule) {
            if (!$rule->canSee($sender)) {
                return false;
            }
        }

        return true;
    }

    private function createSubCommandParameter(SubCommand $subCommand): CommandParameter {
        $values = array_values(array_unique([$subCommand->getName(), ...$subCommand->getAliases()]));

        return CommandParameter::enum(
            $subCommand->getName(),
            new CommandHardEnum($this->uniqueEnumName($subCommand), $values),
            0,
            false
        );
    }

    private function uniqueEnumName(SubCommand $subCommand): string {
        return "subcommand#" . spl_object_id($subCommand);
    }

    public static function updateCommand(string $name, PMCommand $newCommand): void {
        $commandMap = Server::getInstance()->getCommandMap();
        $oldCommand = $commandMap->getCommand($name);

        if ($oldCommand instanceof PMCommand) {
            $commandMap->unregister($oldCommand);
        }

        ToolboxLoader::registerCommand($newCommand);
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->getNetworkSession()->syncAvailableCommands();
        }
    }

    private function refreshHardEnumName(CommandParameter $param): void {
        if (!isset($param->enum) || !$param->enum instanceof CommandHardEnum) {
            return;
        }

        $param->enum = new CommandHardEnum(
            "enum#" . spl_object_id($param->enum),
            $param->enum->getValues()
        );
    }
}
