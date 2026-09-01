<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class ReadabilityAnalysis
{
    /**
     * @param list<Run> $runs
     * @param list<RepeatedChunk> $repeatedChunks
     * @param list<PeriodicPattern> $periodicPatterns
     * @param list<Sequence> $sequences
     * @param list<GroupedSequence> $groupedSequences
     * @param list<ChunkSequence> $chunkSequences
     * @param list<RoundNumber> $roundNumbers
     * @param list<Mirror> $mirrors
     */
    public function __construct(
        public OtpCode $code,
        public DigitFrequencyProfile $digitFrequency,
        public array $runs,
        public array $repeatedChunks,
        public array $periodicPatterns,
        public array $sequences,
        public array $groupedSequences,
        public array $chunkSequences,
        public array $roundNumbers,
        public array $mirrors,
    ) {
    }
}
