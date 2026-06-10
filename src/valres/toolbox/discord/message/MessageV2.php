<?php

declare(strict_types=1);

namespace valres\toolbox\discord\message;

class MessageV2 extends AbstractMessage {
    public const FLAG_IS_COMPONENTS_V2 = 1 << 15;

    public const TYPE_SECTION = 9;
    public const TYPE_TEXT_DISPLAY = 10;
    public const TYPE_THUMBNAIL = 11;
    public const TYPE_MEDIA_GALLERY = 12;
    public const TYPE_FILE = 13;
    public const TYPE_SEPARATOR = 14;
    public const TYPE_CONTAINER = 17;

    protected array $data = [
        "flags" => self::FLAG_IS_COMPONENTS_V2,
        "components" => []
    ];

    public function addComponent(array $component): static {
        $this->data["components"][] = $component;
        return $this;
    }

    public function setComponents(array $components): static {
        $this->data["components"] = $components;
        return $this;
    }

    public function addContainer(array $components, ?int $accentColor = null): static {
        return $this->addComponent(self::container($components, $accentColor));
    }

    public function addTextDisplay(string $content): static {
        return $this->addComponent(self::textDisplay($content));
    }

    public function addSeparator(bool $divider = true, int $spacing = 1): static {
        return $this->addComponent(self::separator($divider, $spacing));
    }

    public function addSection(string $textContent, ?array $accessory = null): static {
        return $this->addComponent(self::section($textContent, $accessory));
    }

    public function addThumbnail(string $url): static {
        return $this->addComponent(self::thumbnail($url));
    }

    public function addMediaGallery(array $items): static {
        return $this->addComponent(self::mediaGallery($items));
    }

    public function addFile(string $attachmentName): static {
        return $this->addComponent(self::file($attachmentName));
    }

    public static function container(array $components, ?int $accentColor = null): array {
        $container = [
            "type" => self::TYPE_CONTAINER,
            "components" => $components
        ];

        if ($accentColor !== null) {
            $container["accent_color"] = $accentColor;
        }

        return $container;
    }

    public static function textDisplay(string $content): array {
        return [
            "type" => self::TYPE_TEXT_DISPLAY,
            "content" => $content
        ];
    }

    public static function separator(bool $divider = true, int $spacing = 1): array {
        return [
            "type" => self::TYPE_SEPARATOR,
            "divider" => $divider,
            "spacing" => $spacing
        ];
    }

    public static function section(string $text, ?array $accessory = null): array {
        $section = [
            "type" => self::TYPE_SECTION,
            "components" => [
                self::textDisplay($text)
            ]
        ];

        if ($accessory !== null) {
            $section["accessory"] = $accessory;
        }

        return $section;
    }

    public static function thumbnail(string $url): array {
        return [
            "type" => self::TYPE_THUMBNAIL,
            "media" => [
                "url" => $url
            ]
        ];
    }

    public static function imageAccessory(string $url): array {
        return self::thumbnail($url);
    }

    public static function mediaGalleryItem(string $url, ?string $description = null): array {
        $item = [
            "media" => [
                "url" => $url
            ]
        ];

        if ($description !== null) {
            $item["description"] = $description;
        }

        return $item;
    }

    public static function mediaGallery(array $items): array {
        return [
            "type" => self::TYPE_MEDIA_GALLERY,
            "items" => $items
        ];
    }

    public static function file(string $attachmentName): array {
        return [
            "type" => self::TYPE_FILE,
            "file" => [
                "url" => "attachment://" . $attachmentName
            ]
        ];
    }
}
