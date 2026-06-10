<?php

declare(strict_types=1);

namespace valres\toolbox\discord\queue;

use valres\toolbox\discord\message\DiscordPayload;
use valres\toolbox\discord\Webhook;

class Queue {
    private int $timer;

    /** @param DiscordPayload[]|array[] $payloads */
    public function __construct(
        private readonly string $name,
        private readonly Webhook $webhook,
        private readonly int $sendIntervalTicks,
        private array $payloads = []
    ) {
        $this->timer = max(1, $sendIntervalTicks);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWebhook(): Webhook {
        return $this->webhook;
    }

    public function getSendIntervalTicks(): int {
        return $this->sendIntervalTicks;
    }

    public function getSendTimer(): int {
        return $this->getSendIntervalTicks();
    }

    /** @return DiscordPayload[]|array[] */
    public function getPayloads(): array {
        return $this->payloads;
    }

    public function addPayload(DiscordPayload|array $payload): void {
        $this->payloads[] = $payload;
    }

    public function clearPayloads(): void {
        $this->payloads = [];
    }

    public function deletePayloads(): void {
        $this->clearPayloads();
    }

    public function flush(): void {
        foreach ($this->payloads as $payload) {
            $this->webhook->send($payload);
        }

        $this->clearPayloads();
        $this->resetTimer();
    }

    public function tick(): void {
        if (--$this->timer > 0) {
            return;
        }

        if ($this->payloads !== []) {
            $this->flush();
            return;
        }

        $this->resetTimer();
    }

    public function update(): void {
        $this->tick();
    }

    private function resetTimer(): void {
        $this->timer = max(1, $this->sendIntervalTicks);
    }
}
