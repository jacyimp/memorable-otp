<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class GroupedSequenceAnalyzer
{
    /**
     * @return list<GroupedSequence>
     */
    public function analyze(OtpCode $code): array
    {
        $value = $code->value;
        $length = $code->length();
        $sequences = [];

        for ($offset = 0; $offset < $length; ++$offset) {
            $remaining = $length - $offset;

            for ($groupLength = 2; $groupLength <= intdiv($remaining, 3); ++$groupLength) {
                $first = $this->groupDigit(
                    value: $value,
                    offset: $offset,
                    length: $groupLength,
                );

                $second = $this->groupDigit(
                    value: $value,
                    offset: $offset + $groupLength,
                    length: $groupLength,
                );

                if ($first === null || $second === null) {
                    continue;
                }

                $step = $second - $first;

                if ($step === 0) {
                    continue;
                }

                $groups = 2;
                $previous = $second;

                while (true) {
                    $groupOffset = $offset + ($groups * $groupLength);

                    if ($groupOffset + $groupLength > $length) {
                        break;
                    }

                    $current = $this->groupDigit(
                        value: $value,
                        offset: $groupOffset,
                        length: $groupLength,
                    );

                    if ($current === null || $current - $previous !== $step) {
                        break;
                    }

                    ++$groups;
                    $previous = $current;
                }

                if ($groups < 3) {
                    continue;
                }

                $sequenceLength = $groups * $groupLength;

                if ($this->isContained($sequences, $offset, $sequenceLength)) {
                    continue;
                }

                $sequences[] = new GroupedSequence(
                    start: $first,
                    step: $step,
                    groupLength: $groupLength,
                    groups: $groups,
                    offset: $offset,
                );
            }
        }

        return $sequences;
    }

    /** @param list<GroupedSequence> $sequences */
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

    private function groupDigit(
        string $value,
        int $offset,
        int $length,
    ): ?int {
        $group = substr($value, $offset, $length);

        if (strspn($group, $group[0]) !== $length) {
            return null;
        }

        return (int) $group[0];
    }
}
