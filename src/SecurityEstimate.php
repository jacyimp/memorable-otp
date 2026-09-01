<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

final readonly class SecurityEstimate
{
    public function __construct(
        public int $length,
        public ReadabilityPreset $preset,
        public float $retainedFraction,
        public bool $exact,
    ) {
    }

    public function rawSearchSpace(): float
    {
        return 10 ** $this->length;
    }

    public function acceptedSearchSpace(): float
    {
        return $this->rawSearchSpace()
            * $this->retainedFraction;
    }

    public function entropyBits(): float
    {
        return log(
            $this->acceptedSearchSpace(),
            2,
        );
    }

    public function entropyLossBits(): float
    {
        return -log(
            $this->retainedFraction,
            2,
        );
    }
}
