<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class DescriptionSegment
{
    public function __construct(
        public string $label,
        public int $offset,
        public int $length,
        public float $cost,
        public float $structuralWeight = 1.0,
    ) {
    }
}
