<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class Sequence
{
    public function __construct(
        public int $start,
        public int $step,
        public int $length,
        public int $offset,
    ) {
    }
}
