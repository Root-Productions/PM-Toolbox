<?php

declare(strict_types=1);

namespace valres\toolbox\camera;

use pocketmine\event\EventPriority;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\CameraPresetsPacket;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\network\mcpe\protocol\types\camera\CameraPreset;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use valres\toolbox\camera\exception\CameraAlreadyRegisteredException;
use valres\toolbox\camera\instruction\AttachCameraInstruction;
use valres\toolbox\camera\instruction\CameraSequence;
use valres\toolbox\camera\instruction\ClearCameraInstruction;
use valres\toolbox\camera\instruction\FadeCameraInstruction;
use valres\toolbox\camera\instruction\FovCameraInstruction;
use valres\toolbox\camera\instruction\SetCameraInstruction;
use valres\toolbox\camera\instruction\ShakeCameraInstruction;
use valres\toolbox\camera\instruction\TargetCameraInstruction;
use valres\toolbox\packet\Packets;

final class Camera {
    private static ?PluginBase $plugin = null;

    private function __construct() {
    }

    public static function register(PluginBase $plugin, int $priority = EventPriority::HIGHEST): void {
        if (self::$plugin !== null) {
            throw CameraAlreadyRegisteredException::forPlugin(self::$plugin);
        }

        self::$plugin = $plugin;

        Packets::createInterceptor($plugin, $priority)
            ->interceptIncoming(
                static function(SetLocalPlayerAsInitializedPacket $packet, NetworkSession $session): bool {
                    self::sendPresetsToSession($session);
                    return true;
                }
            );
    }

    public static function isRegistered(): bool {
        return self::$plugin !== null;
    }

    public static function getPlugin(): ?PluginBase {
        return self::$plugin;
    }

    public static function sendPresets(Player $player): void {
        self::sendPresetsToSession($player->getNetworkSession());
    }

    public static function sendPresetsToSession(NetworkSession $session): void {
        $session->sendDataPacket(CameraPresetsPacket::create(CameraPresets::all()));
    }

    public static function sequence(): CameraSequence {
        return new CameraSequence();
    }

    public static function clear(bool $clear = true, bool $removeTarget = true): ClearCameraInstruction {
        return new ClearCameraInstruction($clear, $removeTarget);
    }

    public static function set(CameraPreset $preset): SetCameraInstruction {
        return new SetCameraInstruction($preset);
    }

    public static function fade(): FadeCameraInstruction {
        return new FadeCameraInstruction();
    }

    public static function fov(float $fieldOfView): FovCameraInstruction {
        return new FovCameraInstruction($fieldOfView);
    }

    public static function shake(float $intensity, float $duration): ShakeCameraInstruction {
        return new ShakeCameraInstruction($intensity, $duration);
    }

    public static function target(int $actorUniqueId): TargetCameraInstruction {
        return new TargetCameraInstruction($actorUniqueId);
    }

    public static function attachToEntity(int $actorUniqueId): AttachCameraInstruction {
        return AttachCameraInstruction::attach($actorUniqueId);
    }

    public static function detachFromEntity(): AttachCameraInstruction {
        return AttachCameraInstruction::detach();
    }
}
