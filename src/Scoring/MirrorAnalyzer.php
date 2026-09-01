<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class MirrorAnalyzer
{
    /**
     * @return list<Mirror>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $length = strlen($value);
        $mirrors = [];

        for ($offset = 0; $offset <= $length - 3; ++$offset) {
            for ($mirrorLength = 3; $offset + $mirrorLength <= $length; ++$mirrorLength) {
                $candidate = substr($value, $offset, $mirrorLength);

                if ($candidate !== strrev($candidate)) {
                    continue;
                }

                $mirrors[] = new Mirror(
                    value: $candidate,
                    offset: $offset,
                );
            }
        }

        return $this->onlyMaximal($mirrors);
    }

    /**
     * @param list<Mirror> $mirrors
     *
     * @return list<Mirror>
     */
    private function onlyMaximal(array $mirrors): array
    {
        return array_values(array_filter(
            $mirrors,
            static function (Mirror $candidate) use ($mirrors): bool {
                $candidateStart = $candidate->offset;
                $candidateEnd = $candidateStart + $candidate->length();

                foreach ($mirrors as $other) {
                    if ($candidate === $other) {
                        continue;
                    }

                    $otherStart = $other->offset;
                    $otherEnd = $otherStart + $other->length();

                    if (
                        $other->length() > $candidate->length()
                        && $otherStart <= $candidateStart
                        && $otherEnd >= $candidateEnd
                    ) {
                        return false;
                    }
                }

                return true;
            },
        ));
    }
}
