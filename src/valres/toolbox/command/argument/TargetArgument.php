<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class TargetArgument extends Argument {
    public function getTypeName(): string {
        return "player";
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    public function canParse(CommandSender $sender, string $test): bool {
        if (preg_match('/^@[aeprs]$/', $test) === 1) {
            return $test !== "@s" || $sender instanceof Entity;
        }

        return preg_match('/^(?!rcon$|console$)[a-zA-Z0-9_]{1,16}$/i', $test) === 1;
    }

    /**
     * @return string|Player|Entity[]|Player[]|null
     */
    public function parse(CommandSender $sender, string $arg): string|Player|array|null {
        if (str_starts_with($arg, "@")) {
            return $this->parseSelector($sender, $arg);
        }

        $player = Server::getInstance()->getPlayerExact($arg);
        if ($player !== null) {
            return $player;
        }
        return $arg;
    }

    /** @return Entity[]|Player[] */
    private function parseSelector(CommandSender $sender, string $selector): array {
        return match ($selector) {
            "@s" => $sender instanceof Entity ? [$sender] : [],
            "@a" => array_values(Server::getInstance()->getOnlinePlayers()),
            "@p" => $this->getNearestPlayer($sender),
            "@r" => $this->getRandomPlayer(),
            "@e" => $this->getEntities(),
            default => []
        };
    }

    /** @return Player[] */
    private function getNearestPlayer(CommandSender $sender): array {
        $players = Server::getInstance()->getOnlinePlayers();
        if ($players === []) {
            return [];
        }

        if (!$sender instanceof Entity) {
            return [array_values($players)[0]];
        }

        $nearest = null;
        $nearestDistance = PHP_FLOAT_MAX;
        foreach ($players as $player) {
            if ($player->getWorld() !== $sender->getWorld()) {
                continue;
            }

            $distance = $player->getPosition()->distanceSquared($sender->getPosition());
            if ($distance < $nearestDistance) {
                $nearest = $player;
                $nearestDistance = $distance;
            }
        }

        return $nearest instanceof Player ? [$nearest] : [];
    }

    /** @return Player[] */
    private function getRandomPlayer(): array {
        $players = array_values(Server::getInstance()->getOnlinePlayers());
        if ($players === []) {
            return [];
        }

        return [$players[array_rand($players)]];
    }

    /** @return Entity[] */
    private function getEntities(): array {
        $entities = [];
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }
}
