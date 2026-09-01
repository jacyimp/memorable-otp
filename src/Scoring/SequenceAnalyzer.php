<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class SequenceAnalyzer
{
    /**
     * @return list<Sequence>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $length = strlen($value);
        $sequences = [];

        for ($offset = 0; $offset <= $length - 3; ++$offset) {
            $start = (int) $value[$offset];
            $step = (int) $value[$offset + 1] - $start;

            if ($step === 0) {
                continue;
            }

            $sequenceLength = 2;

            while ($offset + $sequenceLength < $length) {
                $previous = (int) $value[$offset + $sequenceLength - 1];
                $current = (int) $value[$offset + $sequenceLength];

                if ($current - $previous !== $step) {
                    break;
                }

                ++$sequenceLength;
            }

            if ($sequenceLength < 3) {
                continue;
            }

            if ($this->continuesPreviousSequence($value, $offset, $step)) {
                continue;
            }

            $sequences[] = new Sequence(
                start: $start,
                step: $step,
                length: $sequenceLength,
                offset: $offset,
            );
        }

        return $sequences;
    }

    private function continuesPreviousSequence(
        string $value,
        int $offset,
        int $step,
    ): bool {
        if ($offset === 0) {
            return false;
        }

        return (int) $value[$offset] - (int) $value[$offset - 1] === $step;
    }
}
