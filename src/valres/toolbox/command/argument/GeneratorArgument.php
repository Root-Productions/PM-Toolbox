<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\world\generator\GeneratorManager;

class GeneratorArgument extends OptionsArgument {
    public function __construct(string $name, bool $optional = false) {
        $generators = [];
        foreach (GeneratorManager::getInstance()->getGeneratorList() as $generator) {
            $generators[$generator] = $generator;
        }
        parent::__construct($name, $generators, $optional);
    }
}
