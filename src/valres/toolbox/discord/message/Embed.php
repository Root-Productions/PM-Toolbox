<?php

declare(strict_types=1);

namespace valres\toolbox\discord\message;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class Embed implements DiscordPayload {
    protected array $data = [];

    public function setAuthor(string $name, ?string $url = null, ?string $iconUrl = null): static {
        $this->data["author"] = array_filter([
            "name" => $name,
            "url" => $url,
            "icon_url" => $iconUrl
        ], static fn(mixed $value): bool => $value !== null);

        return $this;
    }

    public function setTitle(string $title): static {
        $this->data["title"] = $title;
        return $this;
    }

    public function setDescription(string $description): static {
        $this->data["description"] = $description;
        return $this;
    }

    public function setUrl(string $url): static {
        $this->data["url"] = $url;
        return $this;
    }

    public function setColor(int $color): static {
        $this->data["color"] = $color;
        return $this;
    }

    public function addField(string $name, string $value, bool $inline = false): static {
        $this->data["fields"][] = [
            "name" => $name,
            "value" => $value,
            "inline" => $inline
        ];

        return $this;
    }

    public function setThumbnail(string $url): static {
        $this->data["thumbnail"] = ["url" => $url];
        return $this;
    }

    public function setImage(string $url): static {
        $this->data["image"] = ["url" => $url];
        return $this;
    }

    public function setFooter(string $text, ?string $iconUrl = null): static {
        $this->data["footer"] = array_filter([
            "text" => $text,
            "icon_url" => $iconUrl
        ], static fn(mixed $value): bool => $value !== null);

        return $this;
    }

    public function setTimestamp(?DateTimeInterface $timestamp = null): static {
        $timestamp ??= new DateTimeImmutable("now", new DateTimeZone("UTC"));
        $this->data["timestamp"] = DateTimeImmutable::createFromInterface($timestamp)
            ->setTimezone(new DateTimeZone("UTC"))
            ->format("Y-m-d\TH:i:s.v\Z");

        return $this;
    }

    public function toArray(): array {
        return $this->data;
    }

    public function asArray(): array {
        return $this->toArray();
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
