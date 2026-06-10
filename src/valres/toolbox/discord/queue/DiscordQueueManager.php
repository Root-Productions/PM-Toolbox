<?php

declare(strict_types=1);

namespace valres\toolbox\discord\queue;

use valres\toolbox\discord\exception\QueueAlreadyRegisteredException;
use valres\toolbox\discord\exception\QueueNotFoundException;
use valres\toolbox\discord\message\DiscordPayload;
use valres\toolbox\discord\Webhook;

final class DiscordQueueManager {
    private static ?self $instance = null;

    /** @var array<string, Queue> */
    private array $queues = [];

    public static function getInstance(): self {
        return self::$instance ??= new self();
    }

    public function get(string $queueName): ?Queue {
        return $this->queues[$queueName] ?? null;
    }

    public function getQueue(string $queueName): ?Queue {
        return $this->get($queueName);
    }

    /** @return array<string, Queue> */
    public function all(): array {
        return $this->queues;
    }

    /** @return array<string, Queue> */
    public function getQueues(): array {
        return $this->all();
    }

    public function has(string $queueName): bool {
        return isset($this->queues[$queueName]);
    }

    public function hasQueue(string $queueName): bool {
        return $this->has($queueName);
    }

    public function register(string $queueName, Webhook $webhook, int $intervalTicks): Queue {
        if ($this->has($queueName)) {
            throw new QueueAlreadyRegisteredException("The queue '{$queueName}' is already registered.");
        }

        return $this->queues[$queueName] = new Queue($queueName, $webhook, $intervalTicks);
    }

    public function registerQueue(string $queueName, Webhook $webhook, int $intervalTicks): Queue {
        return $this->register($queueName, $webhook, $intervalTicks);
    }

    public function unregister(string $queueName): void {
        unset($this->queues[$queueName]);
    }

    public function unregisterQueue(string $queueName): void {
        $this->unregister($queueName);
    }

    public function clear(): void {
        $this->queues = [];
    }

    public function clearQueues(): void {
        $this->clear();
    }

    public function deleteQueues(): void {
        $this->clear();
    }

    public function push(string $queueName, DiscordPayload|array ...$payloads): void {
        $queue = $this->get($queueName);
        if ($queue === null) {
            throw new QueueNotFoundException("The queue '{$queueName}' is not registered.");
        }

        foreach ($payloads as $payload) {
            $queue->addPayload($payload);
        }
    }

    public function addPayload(string $queueName, DiscordPayload|array ...$payloads): void {
        $this->push($queueName, ...$payloads);
    }

    public function addPayloadInQueue(string $queueName, DiscordPayload|array ...$payloads): void {
        $this->push($queueName, ...$payloads);
    }

    public function tick(): void {
        foreach ($this->queues as $queue) {
            $queue->tick();
        }
    }
}
