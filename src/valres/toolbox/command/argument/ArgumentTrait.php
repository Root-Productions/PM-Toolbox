<?php

declare(strict_types=1);

namespace valres\toolbox\command\argument;

use pocketmine\command\CommandSender;
use valres\toolbox\command\ArgumentsList;
use valres\toolbox\command\exception\CommandConfigurationException;
use valres\toolbox\command\result\CommandFailure;

trait ArgumentTrait {
    /** @var array<Argument> */
    private array $arguments = [];

    /** @throws CommandConfigurationException */
    public function addArgument(Argument $argument): self {
        if ($this->getArgument($argument->getName()) !== null) {
            throw new CommandConfigurationException("Duplicate argument '{$argument->getName()}'");
        }

        $previous = end($this->arguments);
        if ($previous instanceof Argument && $previous->isOptional() && !$argument->isOptional()) {
            throw new CommandConfigurationException("Required argument '{$argument->getName()}' cannot be placed after optional argument '{$previous->getName()}'");
        }

        if ($previous instanceof Argument && $previous->getMaxLength() > 1) {
            throw new CommandConfigurationException("Argument '{$previous->getName()}' must be the last argument because it can consume multiple words");
        }

        $this->arguments[] = $argument;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments(): array {
        return $this->arguments;
    }

    public function getArgument(string $name): ?Argument {
        foreach ($this->arguments as $argument) {
            if ($argument->getName() === $name) {
                return $argument;
            }
        }
        return null;
    }

    public function getUsageLine(string $label): string {
        $parts = ['/' . $label];

        foreach ($this->arguments as $argument) {
            $parts[] = $argument->getUsageFormatted();
        }

        return implode(' ', $parts);
    }

    private function parseRawArgs(array $rawArgs): array {
        $processed = [];
        $argDefs = $this->arguments;
        $rawCount = count($rawArgs);
        $argCount = count($argDefs);
        $i = 0;
        $j = 0;

        while ($i < $argCount && $j < $rawCount) {
            $argDef = $argDefs[$i];
            $remainingRaw = $rawCount - $j;
            $remainingArgs = $argCount - $i;

            if ($argDef->getMaxLength() > 1) {
                $toConsume = min($argDef->getMaxLength(), $remainingRaw - ($remainingArgs - 1));
                $value = implode(' ', array_slice($rawArgs, $j, $toConsume));
                $processed[] = $value;
                $j += $toConsume;
                $i++;
                continue;
            }

            $processed[] = $rawArgs[$j];
            $j++;
            $i++;
        }

        while ($j < $rawCount) {
            $processed[] = $rawArgs[$j];
            $j++;
        }

        return $processed;
    }

    protected function validateArguments(CommandSender $sender, array $rawArgs): array {
        $rawArgs = $this->parseRawArgs($rawArgs);
        $providedCount = count($rawArgs);
        $requiredCount = count($this->getRequiredArguments());

        if ($providedCount < $requiredCount) {
            return $this->getMissingArgsErr($requiredCount, $providedCount);
        }

        if ($providedCount > count($this->arguments)) {
            return $this->getExcessArgsErr();
        }

        return $this->validateArgumentTypes($sender, $rawArgs);
    }

    private function getRequiredArguments(): array {
        $arguments = [];
        foreach ($this->arguments as $argument) {
            if (!$argument->isOptional()) {
                $arguments[] = $argument;
            }
        }
        return $arguments;
    }

    private function getMissingArgsErr(int $required, int $provided): array {
        $missing = [];
        for ($i = $provided; $i < $required; $i++) {
            $missing[] = $this->arguments[$i]->getName();
        }

        return [
            'valid' => false,
            'type' => CommandFailure::MISSING_ARGUMENT,
            'message' => 'Missing required arguments: ' . implode(', ', $missing),
            'missing_arguments' => $missing
        ];
    }

    private function getExcessArgsErr(): array {
        return [
            'valid' => false,
            'type' => CommandFailure::INVALID_ARGUMENT,
            'message' => 'Too many arguments (max: ' . count($this->arguments) . ')',
            'max_arguments' => count($this->arguments)
        ];
    }

    private function validateArgumentTypes(CommandSender $sender, array $rawArgs): array {
        $errors = [];

        foreach ($rawArgs as $index => $value) {
            if (!isset($this->arguments[$index])) continue;
            $argument = $this->arguments[$index];

            if (!$argument->canParse($sender, $value)) {
                $errors[] = [
                    'argument' => $argument->getName(),
                    'value' => $value,
                    'message' => "Invalid value for argument '{$argument->getName()}'"
                ];
            }
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'type' => CommandFailure::INVALID_ARGUMENT,
                'message' => 'Invalid arguments provided',
                'argument_errors' => $errors
            ];
        }

        return ['valid' => true];
    }

    protected function parseArguments(CommandSender $sender, array $rawArgs): ArgumentsList {
        $processedArgs = $this->parseRawArgs($rawArgs);

        $parsed = [];
        foreach ($this->arguments as $index => $argDef) {
            $rawValue = $processedArgs[$index] ?? null;
            $parsed[$argDef->getName()] = $rawValue === null ? $argDef->getDefault() : $argDef->parse($sender, $rawValue);
        }

        return new ArgumentsList($sender, $parsed);
    }
}
