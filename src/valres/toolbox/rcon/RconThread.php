<?php

declare(strict_types=1);

namespace valres\toolbox\rcon;

use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use Socket;
use valres\toolbox\rcon\exception\RconProtocolException;

class RconThread extends Thread {
    public string $command = "";
    public string $response = "";

    private bool $stop = false;
    private string $password;
    private int $maxClients;
    private float $authenticationTimeout;
    private int $maxPacketSize;
    private int $selectTimeoutMicroseconds;

    public function __construct(
        private readonly Socket $socket,
        RconSettings $settings,
        private readonly ThreadSafeLogger $logger,
        private readonly Socket $ipcSocket,
        private readonly SleeperHandlerEntry $sleeperEntry
    ) {
        $this->password = $settings->getPassword();
        $this->maxClients = $settings->getMaxClients();
        $this->authenticationTimeout = $settings->getAuthenticationTimeout();
        $this->maxPacketSize = $settings->getMaxPacketSize();
        $this->selectTimeoutMicroseconds = $settings->getSelectTimeoutMicroseconds();
    }

    private function writePacket(Socket $client, int $requestId, int $packetType, string $payload): bool {
        $buffer = (new RconPacket($requestId, $packetType, $payload))->encode();
        $written = 0;
        $length = strlen($buffer);

        while ($written < $length) {
            $result = @socket_write($client, substr($buffer, $written));
            if ($result === false || $result === 0) {
                return false;
            }

            $written += $result;
        }

        return true;
    }

    private function readBytes(Socket $client, int $length): string|false {
        $buffer = "";
        $deadline = microtime(true) + $this->authenticationTimeout;

        while (strlen($buffer) < $length) {
            $chunk = @socket_read($client, $length - strlen($buffer));
            if ($chunk === false) {
                $error = socket_last_error($client);
                if ($error === SOCKET_EAGAIN || $error === SOCKET_EWOULDBLOCK) {
                    if (microtime(true) >= $deadline) {
                        return false;
                    }

                    $read = [$client];
                    $write = null;
                    $except = null;
                    @socket_select($read, $write, $except, 0, $this->selectTimeoutMicroseconds);
                    continue;
                }

                return false;
            }
            if ($chunk === "") {
                return false;
            }

            $buffer .= $chunk;
        }

        return $buffer;
    }

    private function readPacket(Socket $client): ?RconPacket {
        $header = $this->readBytes($client, 4);
        $ip = "unknown";
        $port = 0;
        @socket_getpeername($client, $ip, $port);

        if ($header === false) {
            $error = socket_last_error($client);
            if ($error !== SOCKET_ECONNRESET && $error !== SOCKET_EAGAIN && $error !== SOCKET_EWOULDBLOCK) {
                $this->logger->debug("RCON connection error with $ip:$port: " . trim(socket_strerror($error)));
            }

            return null;
        }

        $size = unpack("V", $header)[1];
        if ($size < RconPacket::MIN_PACKET_SIZE || $size > $this->maxPacketSize) {
            $this->logger->debug("Invalid RCON packet length $size from $ip:$port, disconnecting");
            return null;
        }

        $body = $this->readBytes($client, $size);
        if ($body === false) {
            $error = socket_last_error($client);
            if ($error !== SOCKET_ECONNRESET && $error !== SOCKET_EAGAIN && $error !== SOCKET_EWOULDBLOCK) {
                $this->logger->debug("RCON connection error with $ip:$port: " . trim(socket_strerror($error)));
            }

            return null;
        }

        try {
            return RconPacket::decode($body);
        } catch (RconProtocolException $e) {
            $this->logger->debug("Invalid RCON packet from $ip:$port: " . $e->getMessage());
            return null;
        }
    }

    public function close(): void {
        $this->stop = true;
    }

    protected function onRun(): void {
        /** @var Socket[] $clients */
        $clients = [];
        /** @var bool[] $authenticated */
        $authenticated = [];
        /** @var float[] $timeouts */
        $timeouts = [];

        $nextClientId = 0;
        $notifier = $this->sleeperEntry->createNotifier();

        while (!$this->stop) {
            $read = $clients;
            $read["main"] = $this->socket;
            $read["ipc"] = $this->ipcSocket;
            $write = null;
            $except = null;

            $disconnect = [];

            if (@socket_select($read, $write, $except, 5, 0) > 0) {
                foreach ($read as $id => $sock) {
                    if ($sock === $this->socket) {
                        $client = @socket_accept($this->socket);
                        if ($client === false) {
                            continue;
                        }

                        if (count($clients) >= $this->maxClients) {
                            @socket_close($client);
                            continue;
                        }

                        socket_set_nonblock($client);
                        socket_set_option($client, SOL_SOCKET, SO_KEEPALIVE, 1);

                        $id = $nextClientId++;
                        $clients[$id] = $client;
                        $authenticated[$id] = false;
                        $timeouts[$id] = microtime(true) + $this->authenticationTimeout;
                        continue;
                    }

                    if ($sock === $this->ipcSocket) {
                        @socket_read($sock, 65535);
                        continue;
                    }

                    $packet = $this->readPacket($sock);
                    if ($packet === null) {
                        $disconnect[$id] = $sock;
                        continue;
                    }

                    switch ($packet->getType()) {
                        case RconPacket::SERVERDATA_AUTH:
                            if ($authenticated[$id]) {
                                $disconnect[$id] = $sock;
                                break;
                            }

                            $addr = "unknown";
                            $port = 0;
                            @socket_getpeername($sock, $addr, $port);

                            if (hash_equals($this->password, $packet->getPayload())) {
                                $this->logger->info("Successful RCON connection from: /$addr:$port");
                                $this->writePacket($sock, $packet->getRequestId(), RconPacket::SERVERDATA_AUTH_RESPONSE, "");
                                $authenticated[$id] = true;
                            } else {
                                $this->writePacket($sock, -1, RconPacket::SERVERDATA_AUTH_RESPONSE, "");
                                $this->logger->info("Unsuccessful RCON connection from: /$addr:$port (wrong password)");
                                $disconnect[$id] = $sock;
                            }
                            break;

                        case RconPacket::SERVERDATA_EXECCOMMAND:
                            if (!$authenticated[$id]) {
                                $disconnect[$id] = $sock;
                                break;
                            }

                            if ($packet->getPayload() === "") {
                                break;
                            }

                            $this->command = ltrim($packet->getPayload());
                            $this->synchronized(function() use ($notifier): void {
                                $notifier->wakeupSleeper();
                                $this->wait();
                            });

                            $this->writePacket(
                                $sock,
                                $packet->getRequestId(),
                                RconPacket::SERVERDATA_RESPONSE_VALUE,
                                str_replace("\n", "\r\n", trim($this->response))
                            );

                            $this->response = "";
                            $this->command = "";
                            break;

                        default:
                            $disconnect[$id] = $sock;
                            break;
                    }
                }
            }

            foreach ($authenticated as $id => $status) {
                if (!isset($disconnect[$id]) && !$status && $timeouts[$id] < microtime(true)) {
                    $disconnect[$id] = $clients[$id];
                }
            }

            foreach ($disconnect as $id => $client) {
                $this->disconnectClient($client);
                unset($clients[$id], $authenticated[$id], $timeouts[$id]);
            }
        }

        foreach ($clients as $client) {
            $this->disconnectClient($client);
        }
    }

    private function disconnectClient(Socket $client): void {
        $ip = "unknown";
        $port = 0;
        @socket_getpeername($client, $ip, $port);
        @socket_set_option($client, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
        @socket_shutdown($client, 2);
        @socket_set_block($client);
        @socket_read($client, 1);
        @socket_close($client);
        $this->logger->info("Disconnected RCON client: /$ip:$port");
    }

    public function getThreadName(): string {
        return "Toolbox-RCON";
    }
}
