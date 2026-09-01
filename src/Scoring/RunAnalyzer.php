<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class RunAnalyzer
{
    /**
     * @return list<Run>
     */
    public function analyze(OtpCode $code): array
    {
        $runs = [];
        $length = strlen($code->value);

        for ($start = 0; $start < $length;) {
            $digit = $code->value[$start];
            $end = $start + 1;

            while ($end < $length && $code->value[$end] === $digit) {
                ++$end;
            }

            $runLength = $end - $start;

            if ($runLength >= 2) {
                $runs[] = new Run(
                    digit: $digit,
                    length: $runLength,
                    offset: $start,
                );
            }

            $start = $end;
        }

        return $runs;
    }
}
