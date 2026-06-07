<?php

declare(strict_types=1);

namespace valres\toolbox\packet;

use Closure;
use pocketmine\network\mcpe\protocol\Packet;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use valres\toolbox\packet\exception\InvalidPacketHandlerException;

final class PacketCallbackResolver {
    /**
     * @param callable $handler
     * @param class-string<Packet>[]|class-string<Packet>|null $packetClasses
     * @return class-string<Packet>[]
     * @throws InvalidPacketHandlerException
     */
    public function resolvePacketClasses(callable $handler, array|string|null $packetClasses = null): array {
        if ($packetClasses !== null) {
            return $this->normalizePacketClasses($packetClasses);
        }

        $reflection = $this->reflectCallable($handler);
        $parameters = $reflection->getParameters();

        if (empty($parameters)) {
            throw InvalidPacketHandlerException::missingPacketType($this->describeCallable($handler));
        }

        $type = $parameters[0]->getType();

        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw InvalidPacketHandlerException::missingPacketType($this->describeCallable($handler));
        }

        return $this->normalizePacketClasses($type->getName());
    }

    /**
     * @return class-string<Packet>[]
     * @throws InvalidPacketHandlerException
     */
    private function normalizePacketClasses(array|string $packetClasses): array {
        $classes = is_array($packetClasses) ? $packetClasses : [$packetClasses];
        $normalized = [];

        foreach ($classes as $class) {
            if (!is_string($class) || !is_a($class, Packet::class, true)) {
                throw InvalidPacketHandlerException::invalidPacketClass((string) $class);
            }

            $normalized[$class] = $class;
        }

        return array_values($normalized);
    }

    /**
     * @throws ReflectionException
     */
    private function reflectCallable(callable $handler): ReflectionFunctionAbstract {
        if ($handler instanceof Closure) {
            return new ReflectionFunction($handler);
        }

        if (is_array($handler)) {
            return new ReflectionMethod($handler[0], $handler[1]);
        }

        if (is_string($handler) && str_contains($handler, "::")) {
            [$class, $method] = explode("::", $handler, 2);

            return new ReflectionMethod($class, $method);
        }

        if (is_object($handler) && method_exists($handler, "__invoke")) {
            return new ReflectionMethod($handler, "__invoke");
        }

        return new ReflectionFunction($handler);
    }

    private function describeCallable(callable $handler): string {
        if ($handler instanceof Closure) {
            return "closure";
        }

        if (is_array($handler)) {
            $class = is_object($handler[0]) ? $handler[0]::class : $handler[0];

            return "{$class}::{$handler[1]}";
        }

        if (is_object($handler)) {
            return $handler::class;
        }

        return (string) $handler;
    }
}
