<?php

declare(strict_types=1);

namespace valres\toolbox\discord;

use ReflectionClass;
use ReflectionProperty;
use Stringable;
use UnitEnum;
use valres\toolbox\discord\attribute\DiscordEmbed;
use valres\toolbox\discord\attribute\DiscordField;
use valres\toolbox\discord\attribute\DiscordMessage;
use valres\toolbox\discord\exception\DiscordAttributeException;
use valres\toolbox\discord\message\Embed;
use valres\toolbox\discord\message\Message;

final class DiscordMessageFactory {
    public static function fromObject(object $source): Message {
        $reflection = new ReflectionClass($source);
        $message = self::createMessage($reflection);
        $embed = self::createEmbed($reflection, $source);

        if ($embed->toArray() !== []) {
            $message->addEmbed($embed);
        }

        return $message;
    }

    private static function createMessage(ReflectionClass $reflection): Message {
        $attribute = self::firstAttribute($reflection, DiscordMessage::class);
        $message = new Message($attribute?->content);

        if ($attribute?->username !== null) {
            $message->setUsername($attribute->username);
        }

        if ($attribute?->avatarUrl !== null) {
            $message->setAvatarURL($attribute->avatarUrl);
        }

        return $message;
    }

    private static function createEmbed(ReflectionClass $reflection, object $source): Embed {
        $attribute = self::firstAttribute($reflection, DiscordEmbed::class);
        $embed = new Embed();

        if ($attribute !== null) {
            if ($attribute->title !== null) {
                $embed->setTitle($attribute->title);
            }

            if ($attribute->description !== null) {
                $embed->setDescription($attribute->description);
            }

            if ($attribute->color !== null) {
                $embed->setColor($attribute->color);
            }

            if ($attribute->footer !== null) {
                $embed->setFooter($attribute->footer, $attribute->footerIconUrl);
            }

            if ($attribute->timestamp) {
                $embed->setTimestamp();
            }
        }

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $field = self::firstAttribute($property, DiscordField::class);
            if ($field === null) {
                continue;
            }

            if (!$property->isInitialized($source)) {
                if ($field->skipNull) {
                    continue;
                }

                throw new DiscordAttributeException("Property '{$property->getName()}' is not initialized.");
            }

            $property->setAccessible(true);
            $value = $property->getValue($source);
            if ($value === null && $field->skipNull) {
                continue;
            }

            $embed->addField(
                $field->name ?? self::formatPropertyName($property->getName()),
                self::stringify($value),
                $field->inline
            );
        }

        return $embed;
    }

    private static function stringify(mixed $value): string {
        if ($value === null) {
            return "";
        }

        if (is_bool($value)) {
            return $value ? "true" : "false";
        }

        if ($value instanceof UnitEnum) {
            return $value instanceof \BackedEnum ? (string) $value->value : $value->name;
        }

        if ($value instanceof Stringable || is_scalar($value)) {
            return (string) $value;
        }

        $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $encoded === false ? get_debug_type($value) : $encoded;
    }

    private static function formatPropertyName(string $name): string {
        $name = preg_replace('/(?<!^)[A-Z]/', ' $0', $name) ?? $name;
        return ucfirst(str_replace("_", " ", $name));
    }

    /**
     * @template T of object
     * @param ReflectionClass|ReflectionProperty $reflection
     * @param class-string<T> $attribute
     * @return T|null
     */
    private static function firstAttribute(ReflectionClass|ReflectionProperty $reflection, string $attribute): ?object {
        $attributes = $reflection->getAttributes($attribute);
        return $attributes === [] ? null : $attributes[0]->newInstance();
    }
}
