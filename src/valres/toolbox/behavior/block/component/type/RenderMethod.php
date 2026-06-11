<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum RenderMethod: string {
    case OPAQUE = "opaque";
    case ALPHA_TEST = "alpha_test";
    case ALPHA_TEST_SINGLE_SIDED = "alpha_test_single_sided";
    case ALPHA_TEST_TO_OPAQUE = "alpha_test_to_opaque";
    case ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE = "alpha_test_single_sided_to_opaque";
    case BLEND = "blend";
    case BLEND_TO_OPAQUE = "blend_to_opaque";
    case DOUBLE_SIDED = "double_sided";
}
