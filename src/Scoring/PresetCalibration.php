<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class PresetCalibration
{
    public function __construct(
        public float $threshold,
        public float $retainedFraction,
        public bool $exact,
    ) {
    }
}
