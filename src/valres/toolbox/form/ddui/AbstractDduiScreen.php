<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui;

use Closure;
use pocketmine\player\Player;

abstract class AbstractDduiScreen implements DduiScreen {
    /** @var (Closure(Player, int, static): void)|null */
    private ?Closure $closeHandler = null;

    public function send(Player $player): void {
        DduiManager::send($player, $this);
    }

    /**
     * @param (Closure(Player, int, static): void)|null $handler
     */
    public function onClose(?Closure $handler): static {
        $this->closeHandler = $handler;

        return $this;
    }

    public function handleClose(Player $player, int $reason): void {
        if ($this->closeHandler !== null) {
            ($this->closeHandler)($player, $reason, $this);
        }
    }
}
