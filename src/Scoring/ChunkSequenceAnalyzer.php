<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class ChunkSequenceAnalyzer
{
    /**
     * @return list<ChunkSequence>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $length = $code->length();
        $sequences = [];

        for ($offset = 0; $offset < $length; ++$offset) {
            $remaining = $length - $offset;

            for ($chunkLength = 2; $chunkLength <= intdiv($remaining, 3); ++$chunkLength) {
                $first = $this->chunkValue(
                    value: $value,
                    offset: $offset,
                    length: $chunkLength,
                );

                $second = $this->chunkValue(
                    value: $value,
                    offset: $offset + $chunkLength,
                    length: $chunkLength,
                );

                $step = $second - $first;

                if ($step === 0) {
                    continue;
                }

                if (
                    $this->continuesPreviousSequence(
                        value: $value,
                        offset: $offset,
                        chunkLength: $chunkLength,
                        first: $first,
                        step: $step,
                    )
                ) {
                    continue;
                }

                $chunks = 2;
                $previous = $second;

                while (true) {
                    $chunkOffset = $offset + ($chunks * $chunkLength);

                    if ($chunkOffset + $chunkLength > $length) {
                        break;
                    }

                    $current = $this->chunkValue(
                        value: $value,
                        offset: $chunkOffset,
                        length: $chunkLength,
                    );

                    if ($current - $previous !== $step) {
                        break;
                    }

                    ++$chunks;
                    $previous = $current;
                }

                if ($chunks < 3) {
                    continue;
                }

                $sequenceLength = $chunks * $chunkLength;

                if ($this->isContained($sequences, $offset, $sequenceLength)) {
                    continue;
                }

                $sequences[] = new ChunkSequence(
                    start: $first,
                    step: $step,
                    chunkLength: $chunkLength,
                    chunks: $chunks,
                    offset: $offset,
                );
            }
        }

        return $sequences;
    }

    /** @param list<ChunkSequence> $sequences */
    private function isContained(array $sequences, int $offset, int $length): bool
    {
        foreach ($sequences as $sequence) {
            if (
                $sequence->offset <= $offset
                && $sequence->offset + $sequence->length() >= $offset + $length
            ) {
                return true;
            }
        }

        return false;
    }

    private function chunkValue(
        string $value,
        int $offset,
        int $length,
    ): int {
        return (int) substr($value, $offset, $length);
    }

    private function continuesPreviousSequence(
        string $value,
        int $offset,
        int $chunkLength,
        int $first,
        int $step,
    ): bool {
        $previousOffset = $offset - $chunkLength;

        if ($previousOffset < 0) {
            return false;
        }

        $previous = $this->chunkValue(
            value: $value,
            offset: $previousOffset,
            length: $chunkLength,
        );

        return $first - $previous === $step;
    }
}
