<?php

declare(strict_types=1);

namespace valres\toolbox\discord\message;

class Message extends AbstractMessage {
    public function __construct(?string $content = null) {
        if ($content !== null) {
            $this->setContent($content);
        }
    }

    public function setContent(string $content): static {
        $this->data["content"] = $content;
        return $this;
    }

    public function getContent(): ?string {
        return $this->data["content"] ?? null;
    }

    public function addEmbed(Embed $embed): static {
        $data = $embed->toArray();
        if ($data !== []) {
            $this->data["embeds"][] = $data;
        }

        return $this;
    }

    public function setEmbeds(Embed ...$embeds): static {
        unset($this->data["embeds"]);
        foreach ($embeds as $embed) {
            $this->addEmbed($embed);
        }

        return $this;
    }
}
