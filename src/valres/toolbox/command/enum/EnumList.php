<?php

declare(strict_types=1);

namespace valres\toolbox\command\enum;

use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\Server;
use valres\toolbox\command\exception\CommandException;

class EnumList {
    /** @var array<CommandSoftEnum>  */
    private static array $enums = [];

    public static function getEnumByName(string $name): ?CommandSoftEnum {
        return self::$enums[$name] ?? null;
    }

    /** @return array */
    public static function getEnums(): array {
        return self::$enums;
    }

    /** @throws CommandException */
    public static function addEnum(CommandSoftEnum $enum): void {
        $name = $enum->getName();

        if (isset(self::$enums[$name])) {
            throw new CommandException("Soft enum '{$name}' already exists");
        }

        self::$enums[$name] = $enum;
        self::broadcastUpdate($enum, UpdateSoftEnumPacket::TYPE_ADD);
    }

    public static function getOrCreate(string $name, array $values = []): CommandSoftEnum {
        if (!isset(self::$enums[$name])) {
            self::$enums[$name] = new CommandSoftEnum($name, self::normalizeValues($values));
        }

        return self::$enums[$name];
    }

    public static function setEnumValues(string $name, array $values, bool $broadcast = true): CommandSoftEnum {
        $values = self::normalizeValues($values);
        $enum = new CommandSoftEnum($name, $values);
        $previous = self::$enums[$name] ?? null;

        self::$enums[$name] = $enum;

        if ($broadcast && ($previous === null || $previous->getValues() !== $values)) {
            self::broadcastUpdate($enum, UpdateSoftEnumPacket::TYPE_ADD);
        }

        return $enum;
    }

    public static function removeEnum(string $name, bool $broadcast = true): void {
        $enum = self::$enums[$name] ?? null;
        if ($enum === null) {
            return;
        }

        unset(self::$enums[$name]);
        if ($broadcast) {
            self::broadcastUpdate($enum, UpdateSoftEnumPacket::TYPE_REMOVE);
        }
    }

    private static function normalizeValues(array $values): array {
        $normalized = [];
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== "") {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private static function broadcastUpdate(CommandSoftEnum $enum, int $updateType): void {
        $packet = UpdateSoftEnumPacket::create($enum->getName(), $enum->getValues(), $updateType);

        NetworkBroadcastUtils::broadcastPackets(
            Server::getInstance()->getOnlinePlayers(),
            [$packet]
        );
    }
}
