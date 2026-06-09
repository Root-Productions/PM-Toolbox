<?php

declare(strict_types=1);

namespace valres\toolbox\form\element;

use JsonSerializable;

final class ButtonElement implements JsonSerializable {
    public const IMAGE_TYPE_PATH = "path";
    public const IMAGE_TYPE_URL = "url";

    public function __construct(
        private string $text,
        private string|int|null $name = null,
        private ?string $imageType = null,
        private ?string $imageData = null,
        private mixed $callback = null
    ) {
    }

    public function getName(): string|int|null {
        return $this->name;
    }

    public function getCallback(): ?callable {
        return is_callable($this->callback) ? $this->callback : null;
    }

    public function jsonSerialize(): array {
        $data = ["text" => $this->text];
        if ($this->imageType !== null && $this->imageData !== null) {
            $data["image"] = ["type" => $this->imageType, "data" => $this->imageData];
        }
        return $data;
    }
}
