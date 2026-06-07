<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use ReflectionClass;
use valres\toolbox\manager\attribute\Manager;
use valres\toolbox\manager\attribute\ManagerDependsOn;
use valres\toolbox\manager\attribute\ManagerPriority;
use valres\toolbox\manager\attribute\ManagerPriorityEnum;

final class ManagerMetadataReader {
    public function getName(BaseManager $manager): string {
        $reflection = new ReflectionClass($manager);
        $attributes = $reflection->getAttributes(Manager::class);

        if (empty($attributes)) {
            return $reflection->getShortName();
        }

        return $attributes[0]->newInstance()->name ?? $reflection->getShortName();
    }

    public function getVersion(BaseManager $manager): string {
        $reflection = new ReflectionClass($manager);
        $attributes = $reflection->getAttributes(Manager::class);

        if (empty($attributes)) {
            return "BETA";
        }

        return $attributes[0]->newInstance()->version ?? "BETA";
    }

    public function getPriority(BaseManager $manager): int {
        $reflection = new ReflectionClass($manager);
        $attributes = $reflection->getAttributes(ManagerPriority::class);

        if (empty($attributes)) {
            return ManagerPriorityEnum::PRIORITY_LOW->value;
        }

        $priority = $attributes[0]->newInstance()->value;

        return $priority->value;
    }

    /** @return string[] */
    public function getRequiredManagers(BaseManager $manager): array {
        $reflection = new ReflectionClass($manager);
        $attributes = $reflection->getAttributes(ManagerDependsOn::class);

        if (empty($attributes)) {
            return [];
        }

        return $attributes[0]->newInstance()->dependencies ?? [];
    }
}
