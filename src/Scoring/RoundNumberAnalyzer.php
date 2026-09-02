<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class RoundNumberAnalyzer
{
    /**
     * @return list<RoundNumber>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $codeLength = $code->length();
        $numbers = [];

        for ($offset = 0; $offset < $codeLength - 1; ++$offset) {
            if (
                $value[$offset] === '0'
                || ($offset > 0 && $value[$offset - 1] === '0')
            ) {
                continue;
            }

            if ($value[$offset + 1] !== '0') {
                continue;
            }

            for ($length = 2; $offset + $length <= $codeLength; ++$length) {
                $candidate = substr(
                    $value,
                    $offset,
                    $length,
                );

                if (strspn($candidate, '0', 1) !== $length - 1) {
                    break;
                }

                $trailingZeroes = $this->trailingZeroes($candidate);

                $numbers[] = new RoundNumber(
                    value: $candidate,
                    offset: $offset,
                    trailingZeroes: $trailingZeroes,
                );
            }
        }

        return $numbers;
    }

    private function trailingZeroes(string $value): int
    {
        $trailingZeroes = 0;

        for ($index = strlen($value) - 1; $index >= 0; --$index) {
            if ($value[$index] !== '0') {
                break;
            }

            ++$trailingZeroes;
        }

        return $trailingZeroes;
    }
}
