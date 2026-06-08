<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use valres\toolbox\ToolboxLoader;

class CooldownRule extends Rule {
    /** @var array<string, float> */
    private array $cooldowns = [];

    public function __construct(
        private readonly float $seconds,
        private readonly ?string $message = null
    ) {
    }

    public function canSee(CommandSender $sender): bool {
        return true;
    }

    public function canExecute(CommandSender $sender): bool {
        return !$this->isCoolingDown($sender);
    }

    public function fail(CommandSender $sender): void {
        $remaining = $this->getRemainingSeconds($sender);
        $message = $this->message ?? TextFormat::RED . "You must wait {time}s before using this command again.";

        $sender->sendMessage(str_replace("{time}", (string) max(1, (int) ceil($remaining)), $message));
    }

    public function onExecuted(CommandSender $sender): void {
        if ($this->seconds <= 0) {
            return;
        }

        $key = $this->getSenderKey($sender);
        $expiresAt = microtime(true) + $this->seconds;
        $this->cooldowns[$key] = $expiresAt;
    }

    public function isCoolingDown(CommandSender $sender): bool {
        return $this->getRemainingSeconds($sender) > 0;
    }

    public function getRemainingSeconds(CommandSender $sender): float {
        $key = $this->getSenderKey($sender);
        $expiresAt = $this->cooldowns[$key] ?? 0.0;
        $remaining = $expiresAt - microtime(true);

        if ($remaining <= 0) {
            unset($this->cooldowns[$key]);
            return 0.0;
        }

        return $remaining;
    }

    private function getSenderKey(CommandSender $sender): string {
        if (method_exists($sender, "getUniqueId")) {
            return "player:" . $sender->getUniqueId()->toString();
        }

        return "sender:" . strtolower($sender->getName());
    }
}
