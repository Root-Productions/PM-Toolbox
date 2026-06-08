<?php

declare(strict_types=1);

namespace valres\toolbox\command;

use pocketmine\command\CommandSender;

final class CommandContext {
    public function __construct(
        private readonly CommandSender $sender,
        private readonly ArgumentsList $arguments,
        private readonly string $label,
        private readonly array $rawArguments,
        private readonly Command $command,
        private readonly ?SubCommand $subCommand = null
    ) {
    }

    public function getSender(): CommandSender {
        return $this->sender;
    }

    public function getArguments(): ArgumentsList {
        return $this->arguments;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function getRawArguments(): array {
        return $this->rawArguments;
    }

    public function getCommand(): Command {
        return $this->command;
    }

    public function getSubCommand(): ?SubCommand {
        return $this->subCommand;
    }

    public function hasSubCommand(): bool {
        return $this->subCommand !== null;
    }
}
