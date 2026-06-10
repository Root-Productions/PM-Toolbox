<?php

declare(strict_types=1);

namespace valres\toolbox\discord\message;

abstract class AbstractMessage implements DiscordPayload {
    protected array $data = [];

    public function setUsername(string $username): static {
        $this->data["username"] = $username;
        return $this;
    }

    public function getUsername(): ?string {
        return $this->data["username"] ?? null;
    }

    public function setAvatarURL(string $avatarUrl): static {
        $this->data["avatar_url"] = $avatarUrl;
        return $this;
    }

    public function getAvatarURL(): ?string {
        return $this->data["avatar_url"] ?? null;
    }

    public function toArray(): array {
        return $this->data;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
