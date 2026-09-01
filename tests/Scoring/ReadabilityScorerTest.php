<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\MirrorAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityScore;
use JacyImp\MemorableOtp\Scoring\ReadabilityScorer;
use JacyImp\MemorableOtp\Scoring\RepeatedChunkAnalyzer;
use JacyImp\MemorableOtp\Scoring\RunAnalyzer;
use JacyImp\MemorableOtp\Scoring\SequenceAnalyzer;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadabilityScorer::class)]
#[CoversClass(ReadabilityScore::class)]
final class ReadabilityScorerTest extends TestCase
{
    #[Test]
    public function itReturnsNormalizedComponents(): void
    {
        $score = $this->score('121212');

        self::assertGreaterThanOrEqual(0.0, $score->value);
        self::assertLessThanOrEqual(1.0, $score->value);

        self::assertGreaterThanOrEqual(0.0, $score->symbolSimplicity);
        self::assertLessThanOrEqual(1.0, $score->symbolSimplicity);

        self::assertGreaterThanOrEqual(0.0, $score->structuralSimplicity);
        self::assertLessThanOrEqual(1.0, $score->structuralSimplicity);

        self::assertGreaterThanOrEqual(0.0, $score->transcriptionRisk);
        self::assertLessThanOrEqual(1.0, $score->transcriptionRisk);
    }

    #[Test]
    #[DataProvider('moreReadableCodes')]
    public function itRanksMoreReadableCodesHigher(
        string $moreReadable,
        string $lessReadable,
    ): void {
        self::assertGreaterThan(
            $this->score($lessReadable)->value,
            $this->score($moreReadable)->value,
            sprintf(
                'Expected %s to be more readable than %s.',
                $moreReadable,
                $lessReadable,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function moreReadableCodes(): iterable
    {
        yield 'alternating beats random' => [
            '121212',
            '583917',
        ];

        yield 'paired digits beat random' => [
            '112233',
            '583917',
        ];

        yield 'ascending sequence beats random' => [
            '123456',
            '583917',
        ];

        yield 'mirror beats random' => [
            '123321',
            '583917',
        ];

        yield 'partial sequence beats random' => [
            '123459',
            '583917',
        ];

        yield 'few symbols beat chaotic unique symbols' => [
            '122131',
            '583917',
        ];
    }

    #[Test]
    public function itPenalizesLongHomogeneousRuns(): void
    {
        $score = $this->score('111111');

        self::assertGreaterThan(
            0.0,
            $score->transcriptionRisk,
        );
    }

    private function score(string $code): ReadabilityScore
    {
        return $this->scorer()->score(
            new OtpCode($code),
        );
    }

    private function scorer(): ReadabilityScorer
    {
        $analyzer = new ReadabilityAnalyzer(
            digitFrequencyAnalyzer: new DigitFrequencyAnalyzer(),
            runAnalyzer: new RunAnalyzer(),
            repeatedChunkAnalyzer: new RepeatedChunkAnalyzer(),
            sequenceAnalyzer: new SequenceAnalyzer(),
            mirrorAnalyzer: new MirrorAnalyzer(),
        );

        return new ReadabilityScorer(
            analyzer: $analyzer,
            descriptionCostCalculator: new MinimumDescriptionCostCalculator(
                new StructureCostCalculator(),
            ),
            transcriptionRiskCalculator: new TranscriptionRiskCalculator(),
        );
    }
}
