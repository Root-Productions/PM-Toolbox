<?php

declare(strict_types=1);

namespace valres\toolbox\discord;

use pocketmine\Server;
use valres\toolbox\discord\exception\DiscordWebhookException;
use valres\toolbox\discord\message\DiscordPayload;
use valres\toolbox\discord\task\DiscordWebhookSendTask;

class Webhook {
    public function __construct(
        protected string $url,
        protected bool $withComponents = true
    ) {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new DiscordWebhookException("Invalid Discord webhook URL.");
        }
    }

    public function send(DiscordPayload|array $message): void {
        $payload = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            throw new DiscordWebhookException("Unable to encode Discord webhook payload.");
        }

        Server::getInstance()->getAsyncPool()->submitTask(new DiscordWebhookSendTask(
            $this->getRequestUrl(),
            $payload
        ));
    }

    public function getURL(): string {
        return $this->url;
    }

    public function withComponents(bool $enabled = true): static {
        $this->withComponents = $enabled;
        return $this;
    }

    public function hasComponentsEnabled(): bool {
        return $this->withComponents;
    }

    protected function getRequestUrl(): string {
        if (!$this->withComponents || str_contains($this->url, "with_components=")) {
            return $this->url;
        }

        return $this->url . (str_contains($this->url, "?") ? "&" : "?") . "with_components=true";
    }
}
