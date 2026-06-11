<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

use BackedEnum;

final class MaterialInstance {
    use BlockComponentValue;

    public function __construct(
        private readonly string $texture,
        private readonly BackedEnum|string|null $renderMethod = null,
        private readonly BackedEnum|string|null $tintMethod = null,
        private readonly bool|float|null $ambientOcclusion = null,
        private readonly ?bool $faceDimming = null,
        private readonly ?bool $isotropic = null,
        private readonly ?bool $alphaMaskedTint = null,
        private readonly ?bool $packedBools = null
    ) {
    }

    public function toArray(): array {
        return array_filter([
            "texture" => $this->texture,
            "render_method" => self::enumValue($this->renderMethod),
            "tint_method" => self::enumValue($this->tintMethod),
            "ambient_occlusion" => $this->ambientOcclusion,
            "face_dimming" => $this->faceDimming,
            "isotropic" => $this->isotropic,
            "alpha_masked_tint" => $this->alphaMaskedTint,
            "packed_bools" => $this->packedBools
        ], static fn(mixed $value): bool => $value !== null);
    }
}
