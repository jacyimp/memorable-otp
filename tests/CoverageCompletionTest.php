<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use JacyImp\MemorableOtp\Exception\OtpGenerationFailedException;
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\OtpGenerator;
use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;
use JacyImp\MemorableOtp\Scoring\Description;
use JacyImp\MemorableOtp\Scoring\DescriptionSegment;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyProfile;
use JacyImp\MemorableOtp\Scoring\GroupedSequence;
use JacyImp\MemorableOtp\Scoring\GroupedSequenceAnalyzer;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\PeriodicPattern;
use JacyImp\MemorableOtp\Scoring\PresetCalibration;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\RepeatedChunk;
use JacyImp\MemorableOtp\Scoring\RepeatedChunkAnalyzer;
use JacyImp\MemorableOtp\Scoring\RoundNumber;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;
use JacyImp\MemorableOtp\SecurityEstimate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtpGenerationFailedException::class)]
#[CoversClass(MemorableOtp::class)]
#[CoversClass(OtpGenerator::class)]
#[CoversClass(SecurityEstimate::class)]
#[CoversClass(Description::class)]
#[CoversClass(DescriptionSegment::class)]
#[CoversClass(DigitFrequencyAnalyzer::class)]
#[CoversClass(DigitFrequencyProfile::class)]
#[CoversClass(GroupedSequenceAnalyzer::class)]
#[CoversClass(PeriodicPattern::class)]
#[CoversClass(PresetCalibration::class)]
#[CoversClass(RepeatedChunk::class)]
#[CoversClass(RepeatedChunkAnalyzer::class)]
#[CoversClass(RoundNumber::class)]
#[CoversClass(StructureCostCalculator::class)]
#[CoversClass(TranscriptionRiskCalculator::class)]
#[CoversClass(MinimumDescriptionCostCalculator::class)]
final class CoverageCompletionTest extends TestCase
{
    #[Test]
    public function itExposesSecurityAndFailureDetails(): void
    {
        self::assertSame(
            6,
            (new OtpGenerator())->generate(
                new OtpLength(6),
                ReadabilityPreset::Readable,
            )->length(),
        );

        $estimate = MemorableOtp::security(
            length: 6,
            preset: ReadabilityPreset::UberEasy,
        );

        self::assertEqualsWithDelta(
            log($estimate->acceptedSearchSpace(), 2),
            $estimate->entropyBits(),
            0.000001,
        );

        $exception = OtpGenerationFailedException::afterMaximumAttempts(
            length: new OtpLength(6),
            preset: ReadabilityPreset::UberEasy,
            attempts: 3,
        );

        self::assertSame(
            'Unable to generate a 6-digit uberEasy OTP after 3 attempts.',
            $exception->getMessage(),
        );
    }

    #[Test]
    public function itExplainsDescriptionsAndHandlesEmptyCoverage(): void
    {
        $description = new Description(
            cost: 2.0,
            segments: [
                new DescriptionSegment('Run(1×3)', 0, 3, 1.25),
                new DescriptionSegment('Literal(7)', 3, 1, 1.0, 0.0),
            ],
        );

        self::assertSame('Run(1×3) | Literal(7)', $description->explanation());
        self::assertSame(0.0, $description->structuralCoverage(0));
    }

    #[Test]
    public function itReportsValueObjectMeasurements(): void
    {
        self::assertSame(3.0, (new PeriodicPattern('12', 6, 0))->repetitions());
        self::assertSame(6, (new RepeatedChunk('12', 3, 0))->length());
        self::assertSame(3, (new RoundNumber('100', 0, 2))->length());

        $calibration = new PresetCalibration(0.5, 0.25, true);
        self::assertSame(0.5, $calibration->threshold);

        $profile = (new DigitFrequencyAnalyzer())->analyze(new OtpCode('7'));
        self::assertSame(0.0, $profile->normalizedEntropy());
    }

    #[Test]
    public function itRejectsDegenerateStructureCandidates(): void
    {
        self::assertSame(
            [],
            (new GroupedSequenceAnalyzer())->analyze(new OtpCode('111111')),
        );

        $analyzer = new RepeatedChunkAnalyzer();
        self::assertNotEmpty($analyzer->analyze(new OtpCode('12121212')));
        self::assertNotEmpty($analyzer->analyze(new OtpCode('1234512345')));
    }

    #[Test]
    public function itCalculatesEveryStructureCost(): void
    {
        $calculator = new StructureCostCalculator();

        self::assertSame(2.75, $calculator->forPeriodicPattern(
            new PeriodicPattern('12', 6, 0),
        ));
        self::assertSame(2.0, $calculator->forGroupedSequence(
            new GroupedSequence(1, 1, 2, 3, 0),
        ));
        self::assertSame(2.5, $calculator->forGroupedSequence(
            new GroupedSequence(1, 2, 2, 4, 0),
        ));
        self::assertSame(3.25, $calculator->forGroupedSequence(
            new GroupedSequence(1, 3, 2, 6, 0),
        ));
        self::assertSame(1.5, $calculator->forRoundNumber(
            new RoundNumber('20', 0, 1),
        ));
        self::assertSame(2.25, $calculator->forRoundNumber(
            new RoundNumber('100', 0, 2),
        ));
        self::assertSame(3.0, $calculator->forRoundNumber(
            new RoundNumber('1000', 0, 3),
        ));
    }

    #[Test]
    public function itNormalizesTranscriptionRisk(): void
    {
        $analyzer = new ReadabilityAnalyzer();
        $calculator = new TranscriptionRiskCalculator();

        self::assertSame(
            0.0,
            $calculator->normalized($analyzer->analyze(new OtpCode('1'))),
        );
        self::assertSame(
            1.0,
            $calculator->normalized($analyzer->analyze(new OtpCode('111111'))),
        );
        self::assertSame(
            0.0,
            $calculator->calculate($analyzer->analyze(new OtpCode('121212'))),
        );
    }

    #[Test]
    public function itDescribesEverySupportedStructure(): void
    {
        $calculator = new MinimumDescriptionCostCalculator(
            new StructureCostCalculator(),
        );
        $analyzer = new ReadabilityAnalyzer();

        $codes = [
            '12121212',
            '1212121',
            '12341234',
            '123456',
            '112233',
            '121314',
            '100200',
            '123321',
            '00010',
        ];

        foreach ($codes as $code) {
            $description = $calculator->describe(
                $analyzer->analyze(new OtpCode($code)),
            );

            self::assertNotSame('', $description->explanation());
            self::assertLessThanOrEqual(strlen($code), $description->cost);
        }
    }
}
