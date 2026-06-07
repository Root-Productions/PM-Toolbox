<?php

declare(strict_types=1);

namespace valres\toolbox\command\result;

use pocketmine\command\CommandSender;
use valres\toolbox\command\ArgumentsList;

final class CommandSuccess {
    public function __construct(
        private readonly CommandSender $sender,
        private readonly ArgumentsList $args,
        private readonly string $label
    ) {
    }

    /**
     * @return CommandSender
     */
    public function getSender(): CommandSender {
        return $this->sender;
    }

    /**
     * @return ArgumentsList
     */
    public function getArgs(): ArgumentsList {
        return $this->args;
    }

    /**
     * @return string
     */
    public function getLabel(): string {
        return $this->label;
    }
}