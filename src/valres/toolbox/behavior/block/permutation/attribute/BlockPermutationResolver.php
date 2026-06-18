<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\permutation\attribute;

use Attribute;

/** Marks a permutation resolver for automatic discovery. */
#[Attribute(Attribute::TARGET_CLASS)]
final class BlockPermutationResolver {
}
