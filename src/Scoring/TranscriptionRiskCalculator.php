<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class TranscriptionRiskCalculator
{
    public function calculate(ReadabilityAnalysis $analysis): float
    {
        $risk = 0.0;

        foreach ($analysis->runs as $run) {
            $risk += $this->runRisk($run->length);
        }

        foreach ($analysis->repeatedChunks as $chunk) {
            $risk += $this->repeatedChunkRisk($chunk);
        }

        return $risk;
    }

    public function normalized(ReadabilityAnalysis $analysis): float
    {
        $maximumRisk = $this->runRisk($analysis->code->length());

        if ($maximumRisk === 0.0) {
            return 0.0;
        }

        return min(
            1.0,
            $this->calculate($analysis) / $maximumRisk,
        );
    }

    private function runRisk(int $length): float
    {
        return match (true) {
            $length <= 3 => 0.0,
            $length === 4 => 0.25,
            $length === 5 => 0.5,
            $length === 6 => 0.75,
            default => 0.75 + (($length - 6) * 0.25),
        };
    }

    private function repeatedChunkRisk(RepeatedChunk $chunk): float
    {
        if ($chunk->repetitions <= 3) {
            return 0.0;
        }

        return ($chunk->repetitions - 3) * 0.1;
    }
}
