<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class RepeatedChunk
{
    public function __construct(
        public string $chunk,
        public int $repetitions,
        public int $offset,
    ) {
    }

    public function length(): int
    {
        return strlen($this->chunk) * $this->repetitions;
    }
}
