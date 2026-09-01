<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class ReadabilityAnalyzer
{
    public function __construct(
        private DigitFrequencyAnalyzer $digitFrequencyAnalyzer = new DigitFrequencyAnalyzer(),
        private RunAnalyzer $runAnalyzer = new RunAnalyzer(),
        private RepeatedChunkAnalyzer $repeatedChunkAnalyzer = new RepeatedChunkAnalyzer(),
        private SequenceAnalyzer $sequenceAnalyzer = new SequenceAnalyzer(),
        private GroupedSequenceAnalyzer $groupedSequenceAnalyzer = new GroupedSequenceAnalyzer(),
        private MirrorAnalyzer $mirrorAnalyzer = new MirrorAnalyzer(),
        private ChunkSequenceAnalyzer $chunkSequenceAnalyzer = new ChunkSequenceAnalyzer(),
        private PeriodicPatternAnalyzer $periodicPatternAnalyzer = new PeriodicPatternAnalyzer(),
        private RoundNumberAnalyzer $roundNumberAnalyzer = new RoundNumberAnalyzer(),
    ) {
    }

    public function analyze(OtpCode $code): ReadabilityAnalysis
    {
        return new ReadabilityAnalysis(
            code: $code,
            digitFrequency: $this->digitFrequencyAnalyzer->analyze($code),
            runs: $this->runAnalyzer->analyze($code),
            repeatedChunks: $this->repeatedChunkAnalyzer->analyze($code),
            periodicPatterns: $this->periodicPatternAnalyzer->analyze($code),
            sequences: $this->sequenceAnalyzer->analyze($code),
            groupedSequences: $this->groupedSequenceAnalyzer->analyze($code),
            chunkSequences: $this->chunkSequenceAnalyzer->analyze($code),
            roundNumbers: $this->roundNumberAnalyzer->analyze($code),
            mirrors: $this->mirrorAnalyzer->analyze($code),
        );
    }
}
