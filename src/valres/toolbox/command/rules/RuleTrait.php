<?php

namespace valres\toolbox\command\rules;

use pocketmine\command\CommandSender;

trait RuleTrait {
    /** @var array<Rule>  */
    private array $rules = [];

    public function addRule(Rule $rule): self {
        $this->rules[] = $rule;
        return $this;
    }

    public function getRules(): array {
        return $this->rules;
    }

    public function getRulesByType(string $type): array {
        return array_filter($this->rules, fn($c) => $c instanceof $type);
    }

    public function testRules(CommandSender $sender): RuleResult {
        $failed = [];

        foreach ($this->rules as $rule) {
            if ($rule->canSee($sender)) {
                $rule->success($sender);
            } else {
                $rule->fail($sender);
                $failed[] = $rule;
            }
        }

        return new RuleResult(empty($failed), $failed);
    }
}