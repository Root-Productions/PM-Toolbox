<?php

declare(strict_types=1);

namespace valres\toolbox\utils;

use InvalidArgumentException;
use RuntimeException;

final class ClassScanner {
    public static function sanitizeDirectory(string $directory): string {
        $normalized = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $directory), "/");
        if (str_contains($normalized, "..")) {
            throw new InvalidArgumentException("Invalid path: '{$directory}'");
        }
        return $normalized;
    }

    public static function scan(string $baseDir, string $namespaceRoot, callable $callback): void {
        if (!is_dir($baseDir)) {
            throw new RuntimeException("Directory not found: {$baseDir}");
        }

        foreach (array_diff(scandir($baseDir) ?: [], [".", ".."]) as $file) {
            $path = "{$baseDir}/{$file}";

            if (is_dir($path)) {
                self::scan($path, "{$namespaceRoot}\\{$file}", $callback);
                continue;
            }

            if (pathinfo($path, PATHINFO_EXTENSION) !== "php") {
                continue;
            }

            $class = "{$namespaceRoot}\\" . basename($file, ".php");
            $callback($class);
        }
    }
}