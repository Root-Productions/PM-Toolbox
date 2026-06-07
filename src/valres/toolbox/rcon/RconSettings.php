<?php

declare(strict_types=1);

namespace valres\toolbox\rcon;

use valres\toolbox\rcon\exception\RconException;

final readonly class RconSettings {
    public function __construct(
        private string $address,
        private int    $port,
        private string $password,
        private int    $maxClients = 50,
        private float  $authenticationTimeout = 5.0,
        private int    $listenBacklog = 5,
        private int    $maxPacketSize = 65535,
        private int    $selectTimeoutMicroseconds = 50000
    ) {
        if ($this->address === "") {
            throw new RconException("RCON bind address cannot be empty");
        }
        if ($this->port < 1 || $this->port > 65535) {
            throw new RconException("RCON port must be between 1 and 65535");
        }
        if ($this->password === "") {
            throw new RconException("RCON password cannot be empty");
        }
        if ($this->maxClients < 1) {
            throw new RconException("RCON max clients must be greater than zero");
        }
        if ($this->authenticationTimeout <= 0.0) {
            throw new RconException("RCON authentication timeout must be greater than zero");
        }
        if ($this->listenBacklog < 1) {
            throw new RconException("RCON listen backlog must be greater than zero");
        }
        if ($this->maxPacketSize < RconPacket::MIN_PACKET_SIZE) {
            throw new RconException("RCON max packet size must be at least " . RconPacket::MIN_PACKET_SIZE . " bytes");
        }
        if ($this->selectTimeoutMicroseconds < 1000) {
            throw new RconException("RCON select timeout must be at least 1000 microseconds");
        }
    }

    public static function default(string $password, int $port, string $address = "0.0.0.0"): self {
        return new self($address, $port, $password);
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function getPort(): int {
        return $this->port;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getMaxClients(): int {
        return $this->maxClients;
    }

    public function getAuthenticationTimeout(): float {
        return $this->authenticationTimeout;
    }

    public function getListenBacklog(): int {
        return $this->listenBacklog;
    }

    public function getMaxPacketSize(): int {
        return $this->maxPacketSize;
    }

    public function getSelectTimeoutMicroseconds(): int {
        return $this->selectTimeoutMicroseconds;
    }
}
