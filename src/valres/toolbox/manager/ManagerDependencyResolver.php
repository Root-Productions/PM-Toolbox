<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use valres\toolbox\manager\exception\CircularManagerDependencyException;
use valres\toolbox\manager\exception\DuplicateManagerException;
use valres\toolbox\manager\exception\ManagerException;
use valres\toolbox\manager\exception\MissingManagerDependencyException;

final class ManagerDependencyResolver {
    /**
     * @param BaseManager[] $managers
     * @return BaseManager[]
     * @throws ManagerException
     */
    public function resolve(array $managers): array {
        $this->validate($managers);

        usort($managers, $this->sortByPriorityThenName(...));

        $sorted = [];
        $remaining = $managers;
        $resolvedNames = [];

        while (!empty($remaining)) {
            $progress = false;

            usort($remaining, $this->sortByPriorityThenName(...));

            foreach ($remaining as $index => $manager) {
                if (!empty(array_diff($manager->getRequiredManagers(), $resolvedNames))) {
                    continue;
                }

                $sorted[] = $manager;
                $resolvedNames[] = $manager->getName();
                unset($remaining[$index]);
                $progress = true;
            }

            if (!$progress) {
                throw new CircularManagerDependencyException(
                    array_values(array_map(static fn (BaseManager $manager) => $manager->getName(), $remaining))
                );
            }
        }

        return $sorted;
    }

    /**
     * @param BaseManager[] $managers
     *
     * @throws DuplicateManagerException
     * @throws MissingManagerDependencyException
     */
    private function validate(array $managers): void {
        $managerNames = array_map(static fn (BaseManager $manager) => $manager->getName(), $managers);
        $uniqueNames = [];

        foreach ($managers as $manager) {
            $name = $manager->getName();

            if (isset($uniqueNames[$name])) {
                throw new DuplicateManagerException($name);
            }

            $uniqueNames[$name] = true;
            $missingDependencies = array_diff($manager->getRequiredManagers(), $managerNames);

            if (!empty($missingDependencies)) {
                throw new MissingManagerDependencyException($name, array_values($missingDependencies));
            }
        }
    }

    private function sortByPriorityThenName(BaseManager $a, BaseManager $b): int {
        return ($b->getPriority() <=> $a->getPriority()) ?: ($a->getName() <=> $b->getName());
    }
}
