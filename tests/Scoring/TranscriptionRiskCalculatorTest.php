<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\MirrorAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\RepeatedChunkAnalyzer;
use JacyImp\MemorableOtp\Scoring\RunAnalyzer;
use JacyImp\MemorableOtp\Scoring\SequenceAnalyzer;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranscriptionRiskCalculator::class)]
final class TranscriptionRiskCalculatorTest extends TestCase
{
    #[Test]
    public function itHasNoRiskForOrdinaryCodes(): void
    {
        self::assertSame(
            0.0,
            $this->risk('583917'),
        );
    }

    #[Test]
    public function itDoesNotPenalizeShortRuns(): void
    {
        self::assertSame(
            0.0,
            $this->risk('111223'),
        );
    }

    #[Test]
    public function itPenalizesLongRuns(): void
    {
        self::assertGreaterThan(
            0.0,
            $this->risk('111123'),
        );
    }

    #[Test]
    public function itPenalizesLongerRunsMoreHeavily(): void
    {
        self::assertGreaterThan(
            $this->risk('111123'),
            $this->risk('111112'),
        );

        self::assertGreaterThan(
            $this->risk('111112'),
            $this->risk('111111'),
        );
    }

    #[Test]
    public function itTreatsAlternatingRepetitionAsSaferThanHomogeneousRuns(): void
    {
        self::assertLessThan(
            $this->risk('11111111'),
            $this->risk('12121212'),
        );
    }

    #[Test]
    public function itAddsOnlySmallRiskForManyRepeatedChunks(): void
    {
        self::assertGreaterThan(
            0.0,
            $this->risk('12121212'),
        );

        self::assertLessThan(
            0.5,
            $this->risk('12121212'),
        );
    }

    private function risk(string $code): float
    {
        $analysis = $this->analyzer()->analyze(
            new OtpCode($code),
        );

        return (new TranscriptionRiskCalculator())->calculate($analysis);
    }

    private function analyzer(): ReadabilityAnalyzer
    {
        return new ReadabilityAnalyzer(
            digitFrequencyAnalyzer: new DigitFrequencyAnalyzer(),
            runAnalyzer: new RunAnalyzer(),
            repeatedChunkAnalyzer: new RepeatedChunkAnalyzer(),
            sequenceAnalyzer: new SequenceAnalyzer(),
            mirrorAnalyzer: new MirrorAnalyzer(),
        );
    }
}
