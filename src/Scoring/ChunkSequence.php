<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class ChunkSequence
{
    public function __construct(
        public int $start,
        public int $step,
        public int $chunkLength,
        public int $chunks,
        public int $offset,
    ) {
    }

    public function length(): int
    {
        return $this->chunkLength * $this->chunks;
    }
}
