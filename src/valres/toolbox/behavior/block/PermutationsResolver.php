<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use ReflectionClass;
use valres\toolbox\attribute\AutoLoadPriority;
use valres\toolbox\behavior\block\permutation\attribute\BlockPermutationResolver;
use valres\toolbox\utils\ClassScanner;

abstract class PermutationsResolver {
    /** @var list<self>|null */
    private static ?array $resolvers = null;

    /** @var array<class-string, true> */
    private static array $loadedClasses = [];

    abstract public function supports(BlockBuilder $builder): bool;

    abstract public function resolve(BlockBuilder $builder): void;

    public static function register(self $resolver): void {
        self::getResolvers();
        if (isset(self::$loadedClasses[$resolver::class])) {
            return;
        }

        self::$loadedClasses[$resolver::class] = true;
        self::$resolvers[] = $resolver;
    }

    public static function discover(string $baseDir, string $namespaceRoot): void {
        self::getResolvers();
        self::discoverInto($baseDir, $namespaceRoot);
    }

    public static function resolveAll(BlockBuilder $builder): void {
        foreach (self::getResolvers() as $resolver) {
            if ($resolver->supports($builder)) {
                $resolver->resolve($builder);
            }
        }
    }

    /** @return list<self> */
    public static function getResolvers(): array {
        if (self::$resolvers === null) {
            self::$resolvers = [];
            self::discoverInto(__DIR__ . "/permutation/resolver", __NAMESPACE__ . "\\permutation\\resolver");
        }

        return self::$resolvers;
    }

    private static function discoverInto(string $baseDir, string $namespaceRoot): void {
        $classes = [];
        ClassScanner::scan($baseDir, $namespaceRoot, static function (string $class) use (&$classes): void {
            if (isset(self::$loadedClasses[$class]) || !class_exists($class, true)) {
                return;
            }

            $reflection = new ReflectionClass($class);
            if (
                $reflection->isAbstract()
                || !$reflection->isInstantiable()
                || !$reflection->isSubclassOf(self::class)
                || $reflection->getAttributes(BlockPermutationResolver::class) === []
            ) {
                return;
            }

            $priorityAttributes = $reflection->getAttributes(AutoLoadPriority::class);
            $priority = $priorityAttributes === [] ? 0 : $priorityAttributes[0]->newInstance()->priority;
            $classes[] = [$priority, $reflection];
        });

        usort($classes, static fn(array $a, array $b): int => $a[0] <=> $b[0]);

        foreach ($classes as [, $reflection]) {
            self::$resolvers[] = $reflection->newInstance();
            self::$loadedClasses[$reflection->getName()] = true;
        }
    }
}
