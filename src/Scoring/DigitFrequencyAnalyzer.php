<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class DigitFrequencyAnalyzer
{
    public function analyze(OtpCode $code): DigitFrequencyProfile
    {
        $firstDigit = (int) $code->value[0];
        $frequencies = [$firstDigit => 1];

        foreach (str_split(substr($code->value, 1)) as $digit) {
            $numericDigit = (int) $digit;
            $frequencies[$numericDigit] = ($frequencies[$numericDigit] ?? 0) + 1;
        }

        ksort($frequencies);

        return new DigitFrequencyProfile($frequencies);
    }
}
