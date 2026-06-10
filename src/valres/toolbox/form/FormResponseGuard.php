<?php

declare(strict_types=1);

namespace valres\toolbox\form;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use valres\toolbox\packet\attribute\IncomingPacket;
use valres\toolbox\packet\attribute\OutgoingPacket;
use WeakMap;

final class FormResponseGuard {
    /** @var WeakMap<NetworkSession, array<int, true>> */
    private WeakMap $openForms;

    public function __construct() {
        $this->openForms = new WeakMap();
    }

    /**
     * Tracks form ids sent to each network session.
     *
     * @internal Packet interceptor callback.
     *
     * @param  ModalFormRequestPacket $packet
     * @param  NetworkSession         $session
     *
     * @return bool
     */
    #[OutgoingPacket(ModalFormRequestPacket::class)]
    public function trackOpenForm(ModalFormRequestPacket $packet, NetworkSession $session): bool {
        $forms = $this->openForms[$session] ?? [];
        $forms[$packet->formId] = true;
        $this->openForms[$session] = $forms;

        return true;
    }

    /**
     * Rejects responses for form ids that are not open for the network session.
     *
     * @internal Packet interceptor callback.
     *
     * @param  ModalFormResponsePacket $packet
     * @param  NetworkSession          $session
     *
     * @return bool
     */
    #[IncomingPacket(ModalFormResponsePacket::class)]
    public function guardResponse(ModalFormResponsePacket $packet, NetworkSession $session): bool {
        $forms = $this->openForms[$session] ?? [];
        if (!isset($forms[$packet->formId])) {
            return false;
        }

        unset($forms[$packet->formId]);
        $this->openForms[$session] = $forms;

        return true;
    }
}
