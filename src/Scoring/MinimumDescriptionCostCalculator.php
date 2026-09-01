<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class MinimumDescriptionCostCalculator
{
    private const BOUNDARY_COST = 0.25;

    public function __construct(
        private StructureCostCalculator $structureCostCalculator,
        private LiteralCostCalculator $literalCostCalculator = new LiteralCostCalculator(),
    ) {
    }

    public function calculate(ReadabilityAnalysis $analysis): float
    {
        return $this->describe($analysis)->cost;
    }

    public function describe(ReadabilityAnalysis $analysis): Description
    {
        /** @var array<int, Description> $memo */
        $memo = [];

        return $this->describeFrom(
            analysis: $analysis,
            offset: 0,
            memo: $memo,
        );
    }

    /**
     * @param array<int, Description> $memo
     */
    private function describeFrom(
        ReadabilityAnalysis $analysis,
        int $offset,
        array &$memo,
    ): Description {
        $codeLength = $analysis->code->length();

        if ($offset >= $codeLength) {
            return new Description(
                cost: 0.0,
                segments: [],
            );
        }

        if (isset($memo[$offset])) {
            return $memo[$offset];
        }

        $remainingLength = $codeLength - $offset;
        $remainingValue = substr(
            $analysis->code->value,
            $offset,
        );

        $remainingCost = $this->literalCostCalculator->calculate(
            $remainingValue,
        );

        $best = new Description(
            cost: $remainingCost,
            segments: [
                new DescriptionSegment(
                    label: sprintf('Literal(%s)', $remainingValue),
                    offset: $offset,
                    length: $remainingLength,
                    cost: $remainingCost,
                    structuralWeight: 0.0,
                ),
            ],
        );

        for ($literalLength = 1; $literalLength < $remainingLength; ++$literalLength) {
            $literal = substr(
                $analysis->code->value,
                $offset,
                $literalLength,
            );

            $literalCost = $this->literalCostCalculator->calculate(
                $literal,
            );

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf('Literal(%s)', $literal),
                    offset: $offset,
                    length: $literalLength,
                    cost: $literalCost,
                    structuralWeight: 0.0,
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $literalLength,
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->runs as $run) {
            if ($run->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'Run(%s×%d)',
                        $run->digit,
                        $run->length,
                    ),
                    offset: $run->offset,
                    length: $run->length,
                    cost: $this->structureCostCalculator->forRun($run),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $run->length,
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->repeatedChunks as $chunk) {
            if ($chunk->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'Repeat(%s×%d)',
                        $chunk->chunk,
                        $chunk->repetitions,
                    ),
                    offset: $chunk->offset,
                    length: $chunk->length(),
                    cost: $this->structureCostCalculator->forRepeatedChunk($chunk),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $chunk->length(),
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->periodicPatterns as $pattern) {
            if ($pattern->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'Periodic(%s,length=%d)',
                        $pattern->unit,
                        $pattern->length,
                    ),
                    offset: $pattern->offset,
                    length: $pattern->length,
                    cost: $this->structureCostCalculator->forPeriodicPattern($pattern),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $pattern->length,
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->sequences as $sequence) {
            if ($sequence->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'Sequence(%d,%+d×%d)',
                        $sequence->start,
                        $sequence->step,
                        $sequence->length,
                    ),
                    offset: $sequence->offset,
                    length: $sequence->length,
                    cost: $this->structureCostCalculator->forSequence($sequence),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $sequence->length,
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->groupedSequences as $sequence) {
            if ($sequence->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'GroupedSequence(%d,%+d×%d groups,width=%d)',
                        $sequence->start,
                        $sequence->step,
                        $sequence->groups,
                        $sequence->groupLength,
                    ),
                    offset: $sequence->offset,
                    length: $sequence->length(),
                    cost: $this->structureCostCalculator->forGroupedSequence($sequence),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $sequence->length(),
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->chunkSequences as $sequence) {
            if ($sequence->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'ChunkSequence(%0' . $sequence->chunkLength . 'd,%+d×%d,width=%d)',
                        $sequence->start,
                        $sequence->step,
                        $sequence->chunks,
                        $sequence->chunkLength,
                    ),
                    offset: $sequence->offset,
                    length: $sequence->length(),
                    cost: $this->structureCostCalculator->forChunkSequence($sequence),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $sequence->length(),
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->roundNumbers as $number) {
            if ($number->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'Round(%s)',
                        $number->value,
                    ),
                    offset: $number->offset,
                    length: $number->length(),
                    cost: $this->structureCostCalculator->forRoundNumber($number),
                    structuralWeight: $number->trailingZeroes / $number->length(),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $number->length(),
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        foreach ($analysis->mirrors as $mirror) {
            if ($mirror->offset !== $offset) {
                continue;
            }

            $candidate = $this->prepend(
                segment: new DescriptionSegment(
                    label: sprintf(
                        'Mirror(%s)',
                        $mirror->value,
                    ),
                    offset: $mirror->offset,
                    length: $mirror->length(),
                    cost: $this->structureCostCalculator->forMirror($mirror),
                ),
                rest: $this->describeFrom(
                    analysis: $analysis,
                    offset: $offset + $mirror->length(),
                    memo: $memo,
                ),
            );

            $best = $this->cheaper(
                current: $best,
                candidate: $candidate,
            );
        }

        return $memo[$offset] = $best;
    }

    private function prepend(
        DescriptionSegment $segment,
        Description $rest,
    ): Description {
        $boundaryCost = $rest->segments === []
            ? 0.0
            : self::BOUNDARY_COST;

        return new Description(
            cost: $segment->cost + $boundaryCost + $rest->cost,
            segments: [
                $segment,
                ...$rest->segments,
            ],
        );
    }

    private function cheaper(
        Description $current,
        Description $candidate,
    ): Description {
        if ($candidate->cost < $current->cost) {
            return $candidate;
        }

        if (
            $candidate->cost === $current->cost
            && count($candidate->segments) < count($current->segments)
        ) {
            return $candidate;
        }

        return $current;
    }
}
