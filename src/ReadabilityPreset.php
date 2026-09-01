<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

enum ReadabilityPreset: string
{
    case Readable = 'readable';
    case Easy = 'easy';
    case VeryEasy = 'veryEasy';
    case SuperEasy = 'superEasy';
    case UberEasy = 'uberEasy';

    public function targetAcceptanceRate(): float
    {
        return match ($this) {
            self::Readable => 0.50,
            self::Easy => 0.30,
            self::VeryEasy => 0.20,
            self::SuperEasy => 0.15,
            self::UberEasy => 0.10,
        };
    }

    public function targetEntropyLossBits(): float
    {
        return -log(
            $this->targetAcceptanceRate(),
            2,
        );
    }
}
