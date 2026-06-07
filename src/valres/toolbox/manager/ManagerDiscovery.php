<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use ReflectionException;
use valres\toolbox\manager\exception\ManagerException;
use valres\toolbox\manager\exception\NoManagerFoundException;
use valres\toolbox\utils\AutoLoad;

final class ManagerDiscovery {
    /**
     * @return BaseManager[]
     * @throws ManagerException|ReflectionException
     */
    public function discover(string $managersDirectory): array {
        $managers = [];

        if (!AutoLoad::isInitialized()) {
            AutoLoad::init(false);
        }

        $directory = trim(str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $managersDirectory), DIRECTORY_SEPARATOR);

        AutoLoad::custom($directory, BaseManager::class, function (BaseManager $manager) use (&$managers): void {
            $managers[] = $manager;
        });

        if (empty($managers)) {
            throw new NoManagerFoundException($managersDirectory);
        }

        return $managers;
    }
}
