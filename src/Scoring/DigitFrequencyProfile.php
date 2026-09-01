<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class DigitFrequencyProfile
{
    /**
     * @param non-empty-array<int, int<1, max>> $frequencies
     */
    public function __construct(
        public array $frequencies,
    ) {
    }

    public function uniqueDigits(): int
    {
        return count($this->frequencies);
    }

    public function highestFrequency(): int
    {
        return max($this->frequencies);
    }

    public function length(): int
    {
        return array_sum($this->frequencies);
    }

    public function entropy(): float
    {
        $length = $this->length();
        $entropy = 0.0;

        foreach ($this->frequencies as $frequency) {
            $probability = $frequency / $length;

            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }

    public function normalizedEntropy(): float
    {
        $maximumUniqueDigits = min(10, $this->length());

        if ($maximumUniqueDigits === 1) {
            return 0.0;
        }

        return $this->entropy() / log($maximumUniqueDigits, 2);
    }
}
