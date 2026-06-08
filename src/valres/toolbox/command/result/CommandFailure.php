<?php

declare(strict_types=1);

namespace valres\toolbox\command\result;

use pocketmine\command\CommandSender;
use valres\toolbox\command\rules\Rule;

final class CommandFailure {
    public const INVALID_ARGUMENT = 0;
    public const MISSING_ARGUMENT = 1;
    public const CONSTRAINT_FAILED = 2;
    public const EXECUTION_ERROR = 3;

    public const ERROR_TYPES = [
        self::INVALID_ARGUMENT,
        self::MISSING_ARGUMENT,
        self::CONSTRAINT_FAILED,
        self::EXECUTION_ERROR
    ];

    public function __construct(
        private readonly CommandSender $sender,
        private readonly int $reason,
        private readonly array $data = []
    ) {
        if (!in_array($reason, self::ERROR_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid failure reason ID: $reason");
        }
    }

    public function getSender(): CommandSender {
        return $this->sender;
    }

    public function getReason(): int {
        return $this->reason;
    }

    public function getReasonName(): string {
        return match($this->reason) {
            self::INVALID_ARGUMENT => 'INVALID_ARGUMENT',
            self::MISSING_ARGUMENT => 'MISSING_ARGUMENT',
            self::CONSTRAINT_FAILED => 'CONSTRAINT_FAILED',
            self::EXECUTION_ERROR => 'EXECUTION_ERROR',
            default => 'UNKNOWN_ERROR'
        };
    }

    public function getData(): array {
        return $this->data;
    }

    /** @return Rule[] */
    public function getFailedConstraints(): array {
        return $this->data['failed_constraints'] ?? $this->data['rules_failed'] ?? [];
    }

    public function getMessage(): string {
        $message = $this->data['message'] ?? $this->getDefaultMessage();
        if (isset($this->data['usage'])) {
            $message .= "\nUsage: " . $this->data['usage'];
        }

        return $message;
    }

    private function getDefaultMessage(): string {
        return match($this->reason) {
            self::INVALID_ARGUMENT => 'Invalid argument provided',
            self::MISSING_ARGUMENT => 'Missing required arguments',
            self::CONSTRAINT_FAILED => 'Command constraints not satisfied',
            self::EXECUTION_ERROR => 'An error occurred during command execution',
            default => 'Unknown command error'
        };
    }
}
