<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class PeriodicPattern
{
    public function __construct(
        public string $unit,
        public int $length,
        public int $offset,
    ) {
    }

    public function repetitions(): float
    {
        return $this->length / strlen($this->unit);
    }
}
