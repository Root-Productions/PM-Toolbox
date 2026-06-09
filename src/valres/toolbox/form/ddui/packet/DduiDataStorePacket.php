<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet;

use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\ClientboundDataStorePacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\types\DataStore;

final class DduiDataStorePacket extends ClientboundDataStorePacket implements ClientboundPacket {
    /** @param DataStore[] $values */
    public static function create(array $values): self {
        $packet = new self();
        $packet->values = $values;
        return $packet;
    }

    protected function encodePayload($out): void {
        VarInt::writeUnsignedInt($out, count($this->values));
        foreach ($this->values as $value) {
            VarInt::writeUnsignedInt($out, $value->getTypeId());
            $value->write($out);
        }
    }
}
