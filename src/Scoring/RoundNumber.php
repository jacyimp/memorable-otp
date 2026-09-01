<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class RoundNumber
{
    public function __construct(
        public string $value,
        public int $offset,
        public int $trailingZeroes,
    ) {
    }

    public function length(): int
    {
        return strlen($this->value);
    }
}
