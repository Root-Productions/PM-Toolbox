<?php

declare(strict_types=1);

namespace valres\toolbox\discord\message;

use JsonSerializable;

interface DiscordPayload extends JsonSerializable {
    public function toArray(): array;
}
