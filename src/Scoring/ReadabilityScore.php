<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class ReadabilityScore
{
    public function __construct(
        public float $value,
        public float $symbolSimplicity,
        public float $structuralSimplicity,
        public float $structuralCoverage,
        public float $transcriptionRisk,
    ) {
    }
}
