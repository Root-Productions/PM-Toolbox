<?php

declare(strict_types=1);

namespace valres\toolbox\rcon;

use pocketmine\utils\Binary;
use valres\toolbox\rcon\exception\RconProtocolException;

final class RconPacket {
    public const SERVERDATA_RESPONSE_VALUE = 0;
    public const SERVERDATA_EXECCOMMAND = 2;
    public const SERVERDATA_AUTH_RESPONSE = 2;
    public const SERVERDATA_AUTH = 3;

    public const MIN_PACKET_SIZE = 10;

    public function __construct(
        private readonly int $requestId,
        private readonly int $type,
        private readonly string $payload
    ) {
    }

    public function getRequestId(): int {
        return $this->requestId;
    }

    public function getType(): int {
        return $this->type;
    }

    public function getPayload(): string {
        return $this->payload;
    }

    public function encode(): string {
        $body = Binary::writeLInt($this->requestId) . Binary::writeLInt($this->type) . $this->payload . "\x00\x00";
        return Binary::writeLInt(strlen($body)) . $body;
    }

    public static function decode(string $body): self {
        $size = strlen($body);
        if ($size < self::MIN_PACKET_SIZE) {
            throw new RconProtocolException("RCON packet is too small ($size bytes)");
        }
        if (!str_ends_with($body, "\x00\x00")) {
            throw new RconProtocolException("RCON packet is missing null terminators");
        }

        return new self(
            Binary::readLInt(substr($body, 0, 4)),
            Binary::readLInt(substr($body, 4, 4)),
            substr($body, 8, -2)
        );
    }
}
