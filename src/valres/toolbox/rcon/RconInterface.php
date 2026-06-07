<?php

declare(strict_types=1);

namespace valres\toolbox\rcon;

use pocketmine\network\NetworkInterface;
use pocketmine\snooze\SleeperHandler;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\utils\TextFormat;
use Socket;
use valres\toolbox\rcon\exception\RconException;
use valres\toolbox\rcon\exception\RconSocketException;

class RconInterface implements NetworkInterface {
    private Socket $socket;

    private RconThread $thread;

    private Socket $ipcMainSocket;
    private Socket $ipcThreadSocket;

    public function __construct(RconSettings|int $settings, callable $onCommandCallback, ThreadSafeLogger $logger, SleeperHandler $sleeper, ?string $password = null) {
        if (is_int($settings)) {
            if ($password === null) {
                throw new RconException("RCON password is required when constructing from a port");
            }

            $settings = RconSettings::default($password, $settings);
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw RconSocketException::fromLastError("Failed to create RCON socket");
        }
        $this->socket = $socket;

        if (!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw RconSocketException::fromLastError("Unable to set SO_REUSEADDR on RCON socket", $this->socket);
        }

        if (!@socket_bind($this->socket, $settings->getAddress(), $settings->getPort()) || !@socket_listen($this->socket, $settings->getListenBacklog())) {
            throw RconSocketException::fromLastError("Failed to open RCON socket on " . $settings->getAddress() . ":" . $settings->getPort(), $this->socket);
        }

        socket_set_block($this->socket);

        $ret = @socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $ipc);
        if (!$ret) {
            $err = socket_last_error();
            if (($err !== SOCKET_EPROTONOSUPPORT && $err !== SOCKET_ENOPROTOOPT) || !@socket_create_pair(AF_INET, SOCK_STREAM, 0, $ipc)) {
                throw RconSocketException::fromLastError("Failed to open RCON IPC socket");
            }
        }

        [$this->ipcMainSocket, $this->ipcThreadSocket] = $ipc;

        $sleeperEntry = $sleeper->addNotifier(function() use ($onCommandCallback): void {
            $response = $onCommandCallback($this->thread->command);

            $this->thread->response = TextFormat::clean($response);
            $this->thread->synchronized(function(RconThread $thread): void {
                $thread->notify();
            }, $this->thread);
        });

        $this->thread = new RconThread($this->socket, $settings, $logger, $this->ipcThreadSocket, $sleeperEntry);
    }

    public function start(): void {
        $this->thread->start();
    }

    public function tick(): void {
    }

    public function setName(string $name): void {
    }

    public function shutdown(): void {
        $this->thread->close();
        @socket_write($this->ipcMainSocket, "\x00");
        $this->thread->quit();

        @socket_close($this->socket);
        @socket_close($this->ipcMainSocket);
        @socket_close($this->ipcThreadSocket);
    }
}
