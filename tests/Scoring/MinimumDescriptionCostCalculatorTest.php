<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\MirrorAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\RepeatedChunkAnalyzer;
use JacyImp\MemorableOtp\Scoring\RunAnalyzer;
use JacyImp\MemorableOtp\Scoring\SequenceAnalyzer;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MinimumDescriptionCostCalculator::class)]
final class MinimumDescriptionCostCalculatorTest extends TestCase
{
    #[Test]
    public function itUsesLiteralCostForUnstructuredCodes(): void
    {
        self::assertSame(
            6.0,
            $this->cost('583917'),
        );
    }

    #[Test]
    public function itMakesRepeatedChunksCheaperThanRandomCodes(): void
    {
        self::assertLessThan(
            $this->cost('583917'),
            $this->cost('121212'),
        );
    }

    #[Test]
    public function itMakesSequencesCheaperThanRandomCodes(): void
    {
        self::assertLessThan(
            $this->cost('583917'),
            $this->cost('123456'),
        );
    }

    #[Test]
    public function itMakesMirrorsCheaperThanRandomCodes(): void
    {
        self::assertLessThan(
            $this->cost('583917'),
            $this->cost('123321'),
        );
    }

    #[Test]
    public function itCanCombineMultipleStructures(): void
    {
        self::assertLessThan(
            $this->cost('5839172'),
            $this->cost('1122337'),
        );
    }

    #[Test]
    public function itCanCombineStructureWithLiteralDigits(): void
    {
        self::assertLessThan(
            7.0,
            $this->cost('1234568'),
        );
    }

    #[Test]
    public function itChargesForSwitchingBetweenStructures(): void
    {
        self::assertSame(
            2.5,
            $this->cost('121212'),
        );

        self::assertSame(
            2.75,
            $this->cost('111222'),
        );
    }

    #[Test]
    public function itPrefersOneRepeatedRuleOverMultipleRuns(): void
    {
        self::assertLessThan(
            $this->cost('111222'),
            $this->cost('121212'),
        );
    }

    #[Test]
    public function itUsesAGroupedSequenceForRepeatedPairs(): void
    {
        self::assertSame(
            2.0,
            $this->cost('112233'),
        );
    }

    #[Test]
    public function itCanUseLiteralPrefixBeforeARepetition(): void
    {
        self::assertSame(
            3.75,
            $this->cost('9121212'),
        );
    }

    private function cost(string $code): float
    {
        $analysis = $this->analyzer()->analyze(
            new OtpCode($code),
        );

        return (new MinimumDescriptionCostCalculator(
            new StructureCostCalculator(),
        ))->calculate($analysis);
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
