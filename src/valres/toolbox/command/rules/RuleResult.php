<?php

declare(strict_types=1);

namespace valres\toolbox\command\rules;

class RuleResult {
    public function __construct(
        protected readonly bool $success,
        protected readonly array $failed,
    ) {
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getFailed(): array {
        return $this->failed;
    }
}