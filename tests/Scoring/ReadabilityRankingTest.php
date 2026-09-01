<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\MirrorAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
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
final class ReadabilityRankingTest extends TestCase
{
    #[Test]
    #[DataProvider('obviousRankings')]
    public function itRanksObviouslyMoreReadableCodesHigher(
        string $moreReadable,
        string $lessReadable,
    ): void {
        $moreReadableScore = $this->score($moreReadable);
        $lessReadableScore = $this->score($lessReadable);

        self::assertGreaterThan(
            $lessReadableScore,
            $moreReadableScore,
            sprintf(
                'Expected "%s" (%f) to be more readable than "%s" (%f).',
                $moreReadable,
                $moreReadableScore,
                $lessReadable,
                $lessReadableScore,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function obviousRankings(): iterable
    {
        yield 'alternation beats random' => [
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

        yield 'descending sequence beats random' => [
            '654321',
            '583917',
        ];

        yield 'mirror beats random' => [
            '123321',
            '583917',
        ];

        yield 'zero-separated repetition beats random' => [
            '100100',
            '583917',
        ];

        yield 'mirrored repeated digits beat random' => [
            '770077',
            '583917',
        ];

        yield 'partial sequence beats random' => [
            '123459',
            '583917',
        ];

        yield 'partial repetition beats random' => [
            '121217',
            '583917',
        ];

        yield 'ordered pairs beat scattered repetition' => [
            '112233',
            '112938',
        ];

        yield 'regular alternation beats chaotic low-diversity code' => [
            '121212',
            '122131',
        ];

        yield 'clean sequence beats weakly structured code' => [
            '123456',
            '124859',
        ];

        yield 'balanced runs beat one long homogeneous run' => [
            '111222',
            '111111',
        ];
    }

    private function score(string $code): float
    {
        return $this->scorer()
            ->score(new OtpCode($code))
            ->value;
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
