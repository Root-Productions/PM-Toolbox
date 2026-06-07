<?php

declare(strict_types=1);

namespace valres\toolbox\packet\exception;

final class InvalidPacketHandlerException extends PacketException {
    public static function missingPacketType(string $handler): self {
        return new self("Unable to infer packet type for {$handler}. Provide packet classes explicitly or type the first parameter.");
    }

    public static function invalidPacketClass(string $class): self {
        return new self("Invalid packet class '{$class}'. Packet handlers must target Packet implementations.");
    }
}
