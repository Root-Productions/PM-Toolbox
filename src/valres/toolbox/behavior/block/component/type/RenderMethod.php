<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum RenderMethod: string {
    case OPAQUE = "opaque";
    case ALPHA_TEST = "alpha_test";
    case BLEND = "blend";
    case DOUBLE_SIDED = "double_sided";
}
