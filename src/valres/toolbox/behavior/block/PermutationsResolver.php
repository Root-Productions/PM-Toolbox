<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use valres\toolbox\behavior\block\permutation\resolver\CropsPermutationResolver;
use valres\toolbox\behavior\block\permutation\resolver\HopperPermutationResolver;

abstract class PermutationsResolver {
    /** @var list<self>|null */
    private static ?array $resolvers = null;

    abstract public function resolve(BlockBuilder $builder): void;

    public static function register(self $resolver): void {
        self::getResolvers();
        self::$resolvers[] = $resolver;
    }

    public static function resolveAll(BlockBuilder $builder): void {
        foreach (self::getResolvers() as $resolver) {
            $resolver->resolve($builder);
        }
    }

    /** @return list<self> */
    public static function getResolvers(): array {
        return self::$resolvers ??= [
            new CropsPermutationResolver(),
            new HopperPermutationResolver()
        ];
    }
}
