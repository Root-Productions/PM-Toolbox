<?php

declare(strict_types=1);

namespace valres\toolbox\utils;

use ReflectionClass;
use ReflectionException;
use Throwable;
use valres\toolbox\attribute\AutoLoadCancel;
use valres\toolbox\attribute\AutoLoadPriority;
use valres\toolbox\ToolboxLoader;

final class LoaderRegistry {
    /** @var array<string, true> */
    private array $loadedClasses = [];

    /** @var array<string, int> */
    private array $loadedCount = [
        "command" => 0,
        "listener" => 0,
        "custom" => 0
    ];

    private int $loadingCounter = 0;

    private bool $summaryPrinted = false;

    public function __construct(
        private readonly bool $printSummary = true
    ) {
    }

    /** @throws ReflectionException */
    public function autoload(string $relativeDir, string $requiredType, callable $callback, string $typeName): void {
        $this->loadingCounter++;

        try {
            $plugin = ToolboxLoader::getLoader();
            $main = explode("\\", $plugin->getDescription()->getMain());
            array_pop($main);

            $baseNamespace = implode("\\", $main);
            $relativeDir = trim($relativeDir, "/\\");
            $relativePath = str_replace("\\", "/", $relativeDir);
            $relativeNamespace = str_replace("/", "\\", $relativePath);

            $pluginReflection = new ReflectionClass($plugin);
            $pluginFile = $pluginReflection->getMethod("getFile")->invoke($plugin);

            if (str_ends_with($pluginFile, ".phar")) {
                $pluginFile = "phar://{$pluginFile}";
            }

            $pluginFolder = basename($pluginFile, ".phar");
            $srcPath = rtrim(dirname($pluginFile), "/\\") . "/{$pluginFolder}/src/" . implode("/", $main) . "/{$relativePath}";

            $classes = [];
            ClassScanner::scan($srcPath, "{$baseNamespace}\\{$relativeNamespace}", function (string $class) use (&$classes, $requiredType): void {
                $reflection = $this->getLoadableReflection($class, $requiredType);

                if ($reflection === null) {
                    return;
                }

                $classes[] = [
                    "class" => $class,
                    "reflection" => $reflection,
                    "priority" => $this->getClassPriority($reflection)
                ];
            });

            usort($classes, static fn (array $a, array $b) => $a["priority"] <=> $b["priority"]);

            foreach ($classes as $data) {
                $class = $data["class"];

                if (isset($this->loadedClasses[$class])) {
                    continue;
                }

                try {
                    $instance = $data["reflection"]->newInstance();
                    $callback($instance, $class);
                    $this->loadedClasses[$class] = true;
                    $this->loadedCount[$typeName] = ($this->loadedCount[$typeName] ?? 0) + 1;
                } catch (Throwable $e) {
                    $plugin->getLogger()->error("Failed to load {$class}: {$e->getMessage()}");
                }
            }
        } finally {
            $this->loadingCounter--;
        }
    }

    private function getLoadableReflection(string $class, string $requiredType): ?ReflectionClass {
        if (!class_exists($class, true)) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($class);
            $requiredReflection = new ReflectionClass($requiredType);
        } catch (ReflectionException) {
            return null;
        }

        if ($reflection->isAbstract() || !$reflection->isInstantiable()) {
            return null;
        }

        if (!empty($reflection->getAttributes(AutoLoadCancel::class))) {
            return null;
        }

        if ($requiredReflection->isInterface()) {
            return $reflection->implementsInterface($requiredType) ? $reflection : null;
        }

        return $reflection->isSubclassOf($requiredType) ? $reflection : null;
    }

    private function getClassPriority(ReflectionClass $reflection): int {
        $attributes = $reflection->getAttributes(AutoLoadPriority::class);

        if (empty($attributes)) {
            return 0;
        }

        return $attributes[0]->newInstance()->priority ?? 0;
    }

    public function tryPrintSummary(): void {
        if (!$this->printSummary || $this->summaryPrinted || $this->loadingCounter > 0) {
            return;
        }

        $this->summaryPrinted = true;
        LoggerHelper::printSummary($this->loadedCount);
    }
}
