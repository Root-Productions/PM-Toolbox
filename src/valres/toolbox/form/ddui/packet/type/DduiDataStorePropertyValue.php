<?php

declare(strict_types=1);

namespace valres\toolbox\form\ddui\packet\type;

use pmmp\encoding\LE;

abstract class DduiDataStorePropertyValue {
    abstract public function getTypeId(): int;

    abstract protected function writePayload($out): void;

    public function writeWithType($out): void {
        LE::writeSignedInt($out, $this->getTypeId());
        $this->writePayload($out);
    }
}
