<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class PeriodicPatternAnalyzer
{
    /**
     * @return list<PeriodicPattern>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $length = $code->length();
        $patterns = [];

        for ($offset = 0; $offset < $length; ++$offset) {
            $remaining = $length - $offset;

            for ($unitLength = 2; $unitLength <= intdiv($remaining, 2); ++$unitLength) {
                $unit = substr(
                    $value,
                    $offset,
                    $unitLength,
                );

                $patternLength = $this->matchingLength(
                    value: $value,
                    offset: $offset,
                    unit: $unit,
                );

                if ($patternLength <= $unitLength * 2) {
                    continue;
                }

                if ($patternLength % $unitLength === 0) {
                    continue;
                }

                if (
                    $this->canExtendLeft(
                        value: $value,
                        offset: $offset,
                        unit: $unit,
                    )
                ) {
                    continue;
                }

                $patterns[] = new PeriodicPattern(
                    unit: $unit,
                    length: $patternLength,
                    offset: $offset,
                );
            }
        }

        return $patterns;
    }

    private function matchingLength(
        string $value,
        int $offset,
        string $unit,
    ): int {
        $valueLength = strlen($value);
        $unitLength = strlen($unit);
        $length = 0;

        while ($offset + $length < $valueLength) {
            $expected = $unit[$length % $unitLength];
            $actual = $value[$offset + $length];

            if ($actual !== $expected) {
                break;
            }

            ++$length;
        }

        return $length;
    }

    private function canExtendLeft(
        string $value,
        int $offset,
        string $unit,
    ): bool {
        if ($offset === 0) {
            return false;
        }

        return $value[$offset - 1] === $unit[strlen($unit) - 1];
    }
}
