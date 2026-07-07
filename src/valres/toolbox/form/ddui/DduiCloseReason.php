<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

final class DduiCloseReason {
    public const PROGRAMMATIC = 0;
    public const PROGRAMMATIC_ALL = 1;
    public const CLIENT_CLOSED = 2;
    public const BUSY = 3;
    public const INVALID = 4;

    private function __construct() {}
}
