<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class StructureCostCalculator
{
    public function forRun(Run $run): float
    {
        return 1.0 + $this->lengthMetadataCost($run->length);
    }

    public function forRepeatedChunk(RepeatedChunk $chunk): float
    {
        return strlen($chunk->chunk)
            + $this->repetitionMetadataCost($chunk->repetitions);
    }

    public function forPeriodicPattern(PeriodicPattern $pattern): float
    {
        return strlen($pattern->unit)
            + $this->lengthMetadataCost($pattern->length);
    }

    public function forSequence(Sequence $sequence): float
    {
        return 1.0
            + $this->simpleStepCost($sequence->step)
            + $this->lengthMetadataCost($sequence->length);
    }

    public function forGroupedSequence(GroupedSequence $sequence): float
    {
        return 1.0
            + $this->simpleStepCost($sequence->step)
            + 0.5
            + $this->lengthMetadataCost($sequence->groups);
    }

    public function forChunkSequence(ChunkSequence $sequence): float
    {
        $stepCost = $this->decimalStepCost($sequence->step);

        if ($stepCost === null) {
            return (float) $sequence->length();
        }

        return $sequence->chunkLength
            + $stepCost
            + $this->lengthMetadataCost($sequence->chunks);
    }

    public function forRoundNumber(RoundNumber $number): float
    {
        $discount = match (true) {
            $number->trailingZeroes === 1 => 0.5,
            $number->trailingZeroes === 2 => 0.75,
            default => 1.0,
        };

        return max(
            1.0,
            $number->length() - $discount,
        );
    }

    public function forMirror(Mirror $mirror): float
    {
        $rememberedDigits = (int) ceil($mirror->length() / 2);

        return $rememberedDigits + 0.5;
    }

    private function simpleStepCost(int $step): float
    {
        return match (abs($step)) {
            1 => 0.25,
            2, 5 => 0.5,
            3 => 1.0,
            default => 1.0,
        };
    }

    private function decimalStepCost(int $step): ?float
    {
        $normalizedStep = abs($step);

        while (
            $normalizedStep >= 10
            && $normalizedStep % 10 === 0
        ) {
            $normalizedStep = intdiv($normalizedStep, 10);
        }

        return match ($normalizedStep) {
            1 => 0.25,
            2, 5 => 0.5,
            default => null,
        };
    }

    private function repetitionMetadataCost(int $repetitions): float
    {
        return match (true) {
            $repetitions <= 2 => 0.25,
            $repetitions <= 4 => 0.5,
            default => 0.75,
        };
    }

    private function lengthMetadataCost(int $length): float
    {
        return match (true) {
            $length <= 3 => 0.25,
            $length <= 5 => 0.5,
            default => 0.75,
        };
    }
}
