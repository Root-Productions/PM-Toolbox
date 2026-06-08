<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use valres\toolbox\utils\WorldUtils;

class WorldArgument extends OptionsArgument {
    public function __construct(string $name, bool $optional = false) {
        $worldNames = [];
        foreach (WorldUtils::getAllWorlds() as $worldName) {
            $worldNames[strtolower($worldName)] = $worldName;
        }

        parent::__construct($name, $worldNames, $optional);
    }
}
