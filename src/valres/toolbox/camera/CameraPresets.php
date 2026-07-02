<?php

declare(strict_types=1);

namespace valres\toolbox\camera;

use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use valres\toolbox\camera\exception\CameraPresetException;

final class CameraPresets {
    private const FREE = "free";
    private const FIRST_PERSON = "first_person";
    private const THIRD_PERSON = "third_person";
    private const THIRD_PERSON_FRONT = "third_person_front";
    private const FOLLOW_ORBIT = "follow_orbit";
    private const FIXED_BOOM = "fixed_boom";

    /** @var array<string, CameraPreset>|null */
    private static ?array $presets = null;

    private function __construct() {
    }

    public static function free(): CameraPreset {
        return self::get(self::FREE);
    }

    public static function firstPerson(): CameraPreset {
        return self::get(self::FIRST_PERSON);
    }

    public static function thirdPerson(): CameraPreset {
        return self::get(self::THIRD_PERSON);
    }

    public static function thirdPersonFront(): CameraPreset {
        return self::get(self::THIRD_PERSON_FRONT);
    }

    public static function followOrbit(): CameraPreset {
        return self::get(self::FOLLOW_ORBIT);
    }

    public static function fixedBoom(): CameraPreset {
        return self::get(self::FIXED_BOOM);
    }

    /**
     * Registers a custom preset before players receive the CameraPresetsPacket.
     */
    public static function register(string $name, CameraPreset $preset): void {
        self::init();

        $normalizedName = self::normalizeName($name);

        if (isset(self::$presets[$normalizedName])) {
            throw CameraPresetException::duplicate($normalizedName);
        }

        self::$presets[$normalizedName] = $preset;
    }

    public static function get(string $name): CameraPreset {
        self::init();

        $normalizedName = self::normalizeName($name);

        return self::$presets[$normalizedName] ?? throw CameraPresetException::unknownName($normalizedName);
    }

    /** @return CameraPreset[] */
    public static function all(): array {
        self::init();

        return array_values(self::$presets);
    }

    public static function indexOf(CameraPreset $preset): int {
        foreach (self::all() as $index => $registeredPreset) {
            if ($registeredPreset === $preset || $registeredPreset->getName() === $preset->getName()) {
                return $index;
            }
        }

        throw CameraPresetException::unknown($preset);
    }

    private static function init(): void {
        if (self::$presets !== null) {
            return;
        }

        self::$presets = [
            self::FREE => self::vanilla("minecraft:free", CameraPreset::AUDIO_LISTENER_TYPE_CAMERA),
            self::FIRST_PERSON => self::vanilla("minecraft:first_person", CameraPreset::AUDIO_LISTENER_TYPE_PLAYER),
            self::THIRD_PERSON => self::vanilla("minecraft:third_person", CameraPreset::AUDIO_LISTENER_TYPE_PLAYER),
            self::THIRD_PERSON_FRONT => self::vanilla("minecraft:third_person_front", CameraPreset::AUDIO_LISTENER_TYPE_PLAYER),
            self::FOLLOW_ORBIT => self::vanilla("minecraft:follow_orbit", CameraPreset::AUDIO_LISTENER_TYPE_PLAYER),
            self::FIXED_BOOM => self::vanilla("minecraft:fixed_boom", CameraPreset::AUDIO_LISTENER_TYPE_PLAYER),
        ];
    }

    private static function normalizeName(string $name): string {
        return strtolower(trim($name));
    }

    private static function vanilla(string $identifier, int $audioListenerType): CameraPreset {
        return new CameraPreset(
            $identifier,
            "",
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            0,
            0,
            $audioListenerType,
            null,
            null,
            null
        );
    }
}
