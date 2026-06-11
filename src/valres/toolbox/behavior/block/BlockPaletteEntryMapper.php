<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block;

use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\utils\SingletonTrait;
use RuntimeException;

final class BlockPaletteEntryMapper {
    use SingletonTrait;

    private array $mappings = [];
    private array $entries = [];

    public function nextRuntimeId(): int {
        return 10000 + count($this->entries);
    }

    public function getMappings(): array {
        return $this->mappings;
    }

    /**
     * Returns block palette entries registered through this mapper.
     *
     * @return array<string, array{builder: BlockBuilder, entry: BlockPaletteEntry}>
     */
    public function getBlocks(): array {
        return $this->mappings;
    }

    public function setMappings(array $mappings): void {
        $this->mappings = $mappings;
    }

    public function getEntries(): array {
        return $this->entries;
    }

    public function setEntries(array $entries): void {
        $this->entries = $entries;
    }

    public function map(BlockBuilder $builder, BlockPaletteEntry $entry): void {
        $runtimeId = $builder->getRuntimeId();
        $this->validateRuntimeId($runtimeId);

        if (isset($this->mappings[$runtimeId])) {
            throw new RuntimeException("Block mapping for '{$runtimeId}' is already registered");
        }

        $this->mappings[$runtimeId] = [
            "builder" => $builder,
            "entry" => $entry
        ];
        $this->entries[] = $entry;
    }

    private function validateRuntimeId(string $runtimeId): void {
        if (trim($runtimeId) === "") {
            throw new RuntimeException("Block mapping runtime ID cannot be empty.");
        }
    }
}
