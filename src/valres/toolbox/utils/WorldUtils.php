<?php

declare(strict_types=1);

namespace valres\toolbox\utils;

use FilesystemIterator;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\format\io\data\BaseNbtWorldData;
use pocketmine\world\World;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use valres\toolbox\command\CommandInterceptor;
use valres\toolbox\command\default\world\WorldsCommand;
use valres\toolbox\event\world\WorldDeleteEvent;
use valres\toolbox\ToolboxLoader;
use valres\toolbox\utils\exception\WorldAlreadyExistsException;
use valres\toolbox\utils\exception\WorldNotFoundException;
use valres\toolbox\utils\exception\WorldOperationException;

final class WorldUtils {
    private function __construct() {
    }

    public static function removeWorld(string $name): int {
        self::assertValidWorldName($name);
        self::assertWorldUnlocked($name);

        $server = Server::getInstance();
        $worldManager = $server->getWorldManager();

        if ($worldManager->isWorldLoaded($name)) {
            $world = self::getWorldByNameNonNull($name);
            $defaultWorld = self::getDefaultWorldNonNull();

            foreach ($world->getPlayers() as $player) {
                if ($defaultWorld !== $world) {
                    $player->teleport($defaultWorld->getSpawnLocation());
                }
            }

            if (!$worldManager->unloadWorld($world, true)) {
                throw new WorldOperationException("Unable to unload world '{$name}' before deletion.");
            }
        }

        $worldPath = self::getWorldPath($name);
        if (!is_dir($worldPath)) {
            throw new WorldNotFoundException("World folder '{$name}' does not exist.");
        }

        $removedFiles = self::deleteDirectoryContents($worldPath);
        if (!@rmdir($worldPath)) {
            throw new WorldOperationException("Unable to remove world folder '{$worldPath}'.");
        }

        (new WorldDeleteEvent($name, $worldPath, $removedFiles))->call();
        CommandInterceptor::updateCommand("worlds", new WorldsCommand());

        return $removedFiles;
    }

    public static function renameWorld(string $oldName, string $newName): void {
        self::assertValidWorldName($oldName);
        self::assertValidWorldName($newName);
        self::assertWorldUnlocked($oldName);

        if ($oldName === $newName) {
            return;
        }

        $from = self::getWorldPath($oldName);
        $to = self::getWorldPath($newName);

        if (!is_dir($from)) {
            throw new WorldNotFoundException("World folder '{$oldName}' does not exist.");
        }

        if (file_exists($to)) {
            throw new WorldAlreadyExistsException("World folder '{$newName}' already exists.");
        }

        self::lazyUnloadWorld($oldName, true);

        if (!@rename($from, $to)) {
            throw new WorldOperationException("Unable to rename world '{$oldName}' to '{$newName}'.");
        }

        if (!self::lazyLoadWorld($newName)) {
            throw new WorldOperationException("World '{$newName}' was renamed but could not be loaded.");
        }

        $world = self::getWorldByNameNonNull($newName);
        $worldData = $world->getProvider()->getWorldData();
        if ($worldData instanceof BaseNbtWorldData) {
            $worldData->setName($newName);
            $world->save(true);
        }

        Server::getInstance()->getWorldManager()->unloadWorld($world, true);
        self::lazyLoadWorld($newName);
        CommandInterceptor::updateCommand("worlds", new WorldsCommand());
    }

    public static function duplicateWorld(string $worldName, string $duplicateName): int {
        self::assertValidWorldName($worldName);
        self::assertValidWorldName($duplicateName);

        $server = Server::getInstance();
        $worldManager = $server->getWorldManager();

        if (!$worldManager->isWorldGenerated($worldName)) {
            throw new WorldNotFoundException("World '{$worldName}' is not generated.");
        }

        if ($worldManager->isWorldLoaded($worldName)) {
            self::getWorldByNameNonNull($worldName)->save(true);
        }

        $source = self::getWorldPath($worldName);
        $destination = self::getWorldPath($duplicateName);

        if (file_exists($destination)) {
            throw new WorldAlreadyExistsException("World folder '{$duplicateName}' already exists.");
        }

        $copiedFiles = self::copyDirectory($source, $destination);
        CommandInterceptor::updateCommand("worlds", new WorldsCommand());

        return $copiedFiles;
    }

    public static function backupWorld(string $worldName, ?string $backupName = null): int {
        self::assertValidWorldName($worldName);

        $server = Server::getInstance();
        $worldManager = $server->getWorldManager();

        if (!$worldManager->isWorldGenerated($worldName)) {
            throw new WorldNotFoundException("World '{$worldName}' is not generated.");
        }

        if ($worldManager->isWorldLoaded($worldName)) {
            self::getWorldByNameNonNull($worldName)->save(true);
        }

        $backupName ??= $worldName . "-" . date("Ymd-His");
        self::assertValidBackupName($backupName);

        $destination = self::getBackupPath($backupName);
        if (file_exists($destination)) {
            throw new WorldAlreadyExistsException("Backup '{$backupName}' already exists.");
        }

        return self::copyDirectory(self::getWorldPath($worldName), $destination);
    }

    public static function restoreWorld(string $backupName, ?string $worldName = null): int {
        self::assertValidBackupName($backupName);

        $worldName ??= preg_replace('/-\d{8}-\d{6}$/', "", $backupName) ?: $backupName;
        self::assertValidWorldName($worldName);
        self::assertWorldUnlocked($worldName);

        $source = self::getBackupPath($backupName);
        if (!is_dir($source)) {
            throw new WorldNotFoundException("Backup '{$backupName}' does not exist.");
        }

        $worldManager = Server::getInstance()->getWorldManager();
        if ($worldManager->isWorldLoaded($worldName)) {
            $world = self::getWorldByNameNonNull($worldName);
            $defaultWorld = self::getDefaultWorldNonNull();

            foreach ($world->getPlayers() as $player) {
                if ($defaultWorld !== $world) {
                    $player->teleport($defaultWorld->getSpawnLocation());
                }
            }

            if (!$worldManager->unloadWorld($world, true)) {
                throw new WorldOperationException("Unable to unload world '{$worldName}' before restore.");
            }
        }

        $destination = self::getWorldPath($worldName);
        if (is_dir($destination)) {
            self::deleteDirectory($destination);
        }

        $copiedFiles = self::copyDirectory($source, $destination);
        CommandInterceptor::updateCommand("worlds", new WorldsCommand());

        return $copiedFiles;
    }

    public static function isWorldLocked(string $name): bool {
        self::assertValidWorldName($name);

        $locks = self::readWorldLocks();
        return ($locks[$name] ?? false) === true;
    }

    public static function setWorldLocked(string $name, bool $locked): void {
        self::assertValidWorldName($name);

        $locks = self::readWorldLocks();
        if ($locked) {
            $locks[$name] = true;
        } else {
            unset($locks[$name]);
        }

        self::writeWorldLocks($locks);
    }

    public static function lazyUnloadWorld(string $name, bool $force = false): bool {
        self::assertValidWorldName($name);

        $worldManager = Server::getInstance()->getWorldManager();
        $world = $worldManager->getWorldByName($name);

        return $world !== null && $worldManager->unloadWorld($world, $force);
    }

    public static function lazyLoadWorld(string $name): bool {
        self::assertValidWorldName($name);

        $worldManager = Server::getInstance()->getWorldManager();
        if ($worldManager->isWorldLoaded($name)) {
            return true;
        }

        try {
            $loaded = $worldManager->loadWorld($name, true);
            if ($loaded) {
                CommandInterceptor::updateCommand("worlds", new WorldsCommand());
            }

            return $loaded;
        } catch (Throwable $e) {
            throw new WorldOperationException("Unable to load world '{$name}': {$e->getMessage()}", 0, $e);
        }
    }

    /** @return string[] */
    public static function getAllWorlds(): array {
        $server = Server::getInstance();
        $worldDir = self::getWorldsPath();
        $worlds = [];

        foreach (scandir($worldDir) ?: [] as $entry) {
            if ($entry === "." || $entry === "..") {
                continue;
            }

            $entry = trim($entry);
            if ($entry !== "" && is_dir($worldDir . DIRECTORY_SEPARATOR . $entry) && $server->getWorldManager()->isWorldGenerated($entry)) {
                $worlds[$entry] = $entry;
            }
        }

        foreach ($server->getWorldManager()->getWorlds() as $world) {
            $name = trim($world->getFolderName());
            if ($name !== "") {
                $worlds[$name] = $name;
            }
        }

        sort($worlds);

        return array_values($worlds);
    }

    public static function getLoadedWorldByName(string $name): ?World {
        self::lazyLoadWorld($name);

        return Server::getInstance()->getWorldManager()->getWorldByName($name);
    }

    public static function getWorldByNameNonNull(string $name): World {
        self::assertValidWorldName($name);

        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if (!$world instanceof World) {
            throw new AssumptionFailedError("Required world '{$name}' is not loaded.");
        }

        return $world;
    }

    public static function getDefaultWorldNonNull(): World {
        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        if (!$world instanceof World) {
            throw new AssumptionFailedError("No default world is defined.");
        }

        return $world;
    }

    private static function getWorldsPath(): string {
        return rtrim(Server::getInstance()->getDataPath(), "\\/") . DIRECTORY_SEPARATOR . "worlds";
    }

    private static function getWorldPath(string $name): string {
        return self::getWorldsPath() . DIRECTORY_SEPARATOR . $name;
    }

    private static function getBackupsPath(): string {
        return rtrim(ToolboxLoader::getLoader()->getDataFolder(), "\\/") . DIRECTORY_SEPARATOR . "world-backups";
    }

    private static function getBackupPath(string $name): string {
        return self::getBackupsPath() . DIRECTORY_SEPARATOR . $name;
    }

    private static function getLocksPath(): string {
        return rtrim(ToolboxLoader::getLoader()->getDataFolder(), "\\/") . DIRECTORY_SEPARATOR . "world-locks.json";
    }

    private static function assertValidWorldName(string $name): void {
        if (trim($name) === "" || str_contains($name, "..") || str_contains($name, "/") || str_contains($name, "\\")) {
            throw new WorldOperationException("Invalid world name '{$name}'.");
        }
    }

    private static function assertValidBackupName(string $name): void {
        if (trim($name) === "" || str_contains($name, "..") || str_contains($name, "/") || str_contains($name, "\\")) {
            throw new WorldOperationException("Invalid backup name '{$name}'.");
        }
    }

    private static function assertWorldUnlocked(string $name): void {
        if (self::isWorldLocked($name)) {
            throw new WorldOperationException("World '{$name}' is locked.");
        }
    }

    /** @return array<string, bool> */
    private static function readWorldLocks(): array {
        $path = self::getLocksPath();
        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            return [];
        }

        $locks = [];
        foreach ($data as $name => $locked) {
            if (is_string($name) && $locked === true) {
                $locks[$name] = true;
            }
        }

        return $locks;
    }

    /** @param array<string, bool> $locks */
    private static function writeWorldLocks(array $locks): void {
        $directory = dirname(self::getLocksPath());
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new WorldOperationException("Unable to create directory '{$directory}'.");
        }

        $json = json_encode($locks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || @file_put_contents(self::getLocksPath(), $json . PHP_EOL) === false) {
            throw new WorldOperationException("Unable to write world locks.");
        }
    }

    private static function deleteDirectory(string $directory): int {
        $removedFiles = self::deleteDirectoryContents($directory);
        if (!@rmdir($directory)) {
            throw new WorldOperationException("Unable to remove directory '{$directory}'.");
        }

        return $removedFiles;
    }

    private static function deleteDirectoryContents(string $directory): int {
        $removedFiles = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($files as $fileInfo) {
            $path = $fileInfo->getPathname();

            if ($fileInfo->isDir()) {
                if (!@rmdir($path)) {
                    throw new WorldOperationException("Unable to remove directory '{$path}'.");
                }
            } elseif (!@unlink($path)) {
                throw new WorldOperationException("Unable to remove file '{$path}'.");
            }

            $removedFiles++;
        }

        return $removedFiles;
    }

    private static function copyDirectory(string $source, string $destination): int {
        if (!is_dir($source)) {
            throw new WorldNotFoundException("Source world folder '{$source}' does not exist.");
        }

        if (!@mkdir($destination, 0777, true) && !is_dir($destination)) {
            throw new WorldOperationException("Unable to create world folder '{$destination}'.");
        }

        $copiedFiles = 0;
        $sourceLength = strlen(rtrim($source, "\\/"));
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($files as $fileInfo) {
            $sourcePath = $fileInfo->getPathname();
            $relativePath = ltrim(substr($sourcePath, $sourceLength), "\\/");
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($fileInfo->isDir()) {
                if (!@mkdir($targetPath, 0777, true) && !is_dir($targetPath)) {
                    throw new WorldOperationException("Unable to create directory '{$targetPath}'.");
                }
                continue;
            }

            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir) && !@mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
                throw new WorldOperationException("Unable to create directory '{$targetDir}'.");
            }

            if (!@copy($sourcePath, $targetPath)) {
                throw new WorldOperationException("Unable to copy '{$sourcePath}' to '{$targetPath}'.");
            }

            $copiedFiles++;
        }

        return $copiedFiles;
    }
}
