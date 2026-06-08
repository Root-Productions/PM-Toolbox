<?php

declare(strict_types=1);

namespace valres\toolbox\manager;

use FilesystemIterator;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use Throwable;
use valres\toolbox\manager\attribute\AutoRegister;
use valres\toolbox\manager\attribute\AutoRegisterAll;
use valres\toolbox\manager\attribute\AutoRegisterType;
use valres\toolbox\ToolboxLoader;

final class ManagerComponentRegistrar {
    public function __construct(
        private readonly ManagerLogger $logger
    ) {
    }

    /**
     * @return array{version: string, commands: int, listeners: int, time: float, summary: ?array}
     * @throws ReflectionException
     */
    public function register(BaseManager $manager, float $time): array {
        $reflection = new ReflectionClass($manager);
        $file = $reflection->getFileName();
        $commandsCount = 0;
        $listenersCount = 0;

        if ($file !== false) {
            $managerDir = dirname($file);

            foreach ($this->getAutoRegisterTypes($reflection) as $type) {
                $dir = $managerDir . DIRECTORY_SEPARATOR . $type->value;

                if (!is_dir($dir)) {
                    continue;
                }

                $count = match ($type) {
                    AutoRegisterType::COMMANDS => $this->registerCommands($dir),
                    AutoRegisterType::LISTENERS => $this->registerListeners($dir),
                };

                if ($type === AutoRegisterType::COMMANDS) {
                    $commandsCount += $count;
                } else {
                    $listenersCount += $count;
                }
            }
        }

        return [
            "version" => $manager->getVersion(),
            "commands" => $commandsCount,
            "listeners" => $listenersCount,
            "time" => $time,
            "summary" => $manager->getInitializationSummary()
        ];
    }

    /** @return AutoRegisterType[] */
    private function getAutoRegisterTypes(ReflectionClass $reflection): array {
        $types = [];

        if (!empty($reflection->getAttributes(AutoRegisterAll::class))) {
            $types[] = AutoRegisterType::COMMANDS;
            $types[] = AutoRegisterType::LISTENERS;
        }

        foreach ($reflection->getAttributes(AutoRegister::class) as $attribute) {
            $types[] = $attribute->newInstance()->type;
        }

        $uniqueTypes = [];

        foreach ($types as $type) {
            $uniqueTypes[$type->value] = $type;
        }

        return array_values($uniqueTypes);
    }

    /** @throws ReflectionException */
    private function registerCommands(string $dir): int {
        $loader = ToolboxLoader::getLoader();

        return $this->registerClassesFromDir(
            $dir,
            Command::class,
            fn (Command $command) => ToolboxLoader::registerCommand($command)
        );
    }

    /** @throws ReflectionException */
    private function registerListeners(string $dir): int {
        $loader = ToolboxLoader::getLoader();

        return $this->registerClassesFromDir(
            $dir,
            Listener::class,
            fn (Listener $listener) => $loader->getServer()->getPluginManager()->registerEvents($listener, $loader)
        );
    }

    /** @throws ReflectionException */
    private function registerClassesFromDir(string $dir, string $expectedType, callable $onRegister): int {
        $count = 0;
        $srcDir = $this->getSrcDirectory();

        if ($srcDir === null) {
            return 0;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== "php") {
                continue;
            }

            $class = $this->getClassFromFile($file->getPathname(), $srcDir);

            if ($class === null || !class_exists($class) || !is_subclass_of($class, $expectedType)) {
                continue;
            }

            try {
                $onRegister(new $class());
                $count++;
            } catch (Throwable $e) {
                $this->logger->error("Failed to load class {$class}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    private function getSrcDirectory(): ?string {
        static $srcDir = null;

        if ($srcDir !== null) {
            return $srcDir;
        }

        try {
            $loader = ToolboxLoader::getLoader();
            $reflection = new ReflectionClass($loader);
            $pluginFile = $reflection->getMethod("getFile")->invoke($loader);
            $srcDir = realpath($pluginFile . "/src") ?: null;
        } catch (Throwable) {
            return null;
        }

        return $srcDir;
    }

    private function getClassFromFile(string $filepath, string $srcDir): ?string {
        $relativePath = str_replace(
            [$srcDir, DIRECTORY_SEPARATOR, ".php"],
            ["", "\\", ""],
            $filepath
        );

        return ltrim($relativePath, "\\") ?: null;
    }
}
