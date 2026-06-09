<?php

declare(strict_types=1);

namespace valres\toolbox\form\attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class FormField {
    public const LABEL = "label";
    public const HEADER = "header";
    public const DIVIDER = "divider";
    public const TOGGLE = "toggle";
    public const INPUT = "input";
    public const SLIDER = "slider";
    public const DROPDOWN = "dropdown";
    public const STEP_SLIDER = "step_slider";

    /** @param string[] $options */
    public function __construct(
        public readonly string $type,
        public readonly string $text = "",
        public readonly ?string $name = null,
        public readonly int $order = 0,
        public readonly array $options = [],
        public readonly int|float|null $min = null,
        public readonly int|float|null $max = null,
        public readonly int|float|null $step = null,
        public readonly string $placeholder = "",
        public readonly ?int $defaultIndex = null,
        public readonly bool $returnOption = true
    ) {
    }
}
