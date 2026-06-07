<?php

declare(strict_types=1);

namespace valres\toolbox\rcon;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;

class RconCommandSender extends ConsoleCommandSender {
    private string $messages = "";

    public function sendMessage(Translatable|string $message): void {
        if ($message instanceof Translatable) {
            $message = $this->getServer()->getLanguage()->translate($message);
        }

        $this->messages .= trim($message, "\r\n") . "\n";
    }

    public function getMessage(): string {
        return $this->messages;
    }

    public function getName(): string {
        return "Toolbox-RCON";
    }
}
