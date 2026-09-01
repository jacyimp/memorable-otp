<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class Run
{
    public function __construct(
        public string $digit,
        public int $length,
        public int $offset,
    ) {
    }
}
