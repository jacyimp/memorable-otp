<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class RepeatedChunkAnalyzer
{
    /**
     * @return list<RepeatedChunk>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $length = strlen($value);
        $repetitions = [];

        for ($offset = 0; $offset < $length; ++$offset) {
            $remaining = $length - $offset;

            for ($chunkLength = 2; $chunkLength <= intdiv($remaining, 2); ++$chunkLength) {
                $chunk = substr($value, $offset, $chunkLength);

                if ($this->isItselfRepeated($chunk)) {
                    continue;
                }

                if ($this->canExtendLeft($value, $offset, $chunk)) {
                    continue;
                }

                $count = 1;
                $position = $offset + $chunkLength;

                while (
                    $position + $chunkLength <= $length
                    && substr($value, $position, $chunkLength) === $chunk
                ) {
                    ++$count;
                    $position += $chunkLength;
                }

                if ($count < 2) {
                    continue;
                }

                $repetitions[] = new RepeatedChunk(
                    chunk: $chunk,
                    repetitions: $count,
                    offset: $offset,
                );
            }
        }

        return $repetitions;
    }

    private function canExtendLeft(
        string $value,
        int $offset,
        string $chunk,
    ): bool {
        if ($offset === 0) {
            return false;
        }

        return $value[$offset - 1] === $chunk[strlen($chunk) - 1];
    }

    private function isItselfRepeated(string $chunk): bool
    {
        $length = strlen($chunk);

        for ($unitLength = 1; $unitLength <= intdiv($length, 2); ++$unitLength) {
            if ($length % $unitLength !== 0) {
                continue;
            }

            $unit = substr($chunk, 0, $unitLength);

            if (str_repeat($unit, intdiv($length, $unitLength)) === $chunk) {
                return true;
            }
        }

        return false;
    }
}
