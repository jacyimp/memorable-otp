<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use InvalidArgumentException;
use JacyImp\MemorableOtp\Exception\OtpGenerationFailedException;
use JacyImp\MemorableOtp\OtpGenerator;
use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\PresetCalibrationProvider;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityScorer;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;
use JacyImp\MemorableOtp\Tests\Generation\SequenceCandidateGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtpGenerator::class)]
final class OtpGeneratorTest extends TestCase
{
    #[Test]
    public function itUsesItsDocumentedDefaultsAndInjectedCollaborators(): void
    {
        $scorer = new ReadabilityScorer(
            new ReadabilityAnalyzer(),
            new MinimumDescriptionCostCalculator(new StructureCostCalculator()),
            new TranscriptionRiskCalculator(),
        );
        $calibrations = new PresetCalibrationProvider();
        $generator = new OtpGenerator(
            scorer: $scorer,
            calibrationProvider: $calibrations,
        );

        self::assertSame(1000, $this->property($generator, 'maxAttempts'));
        self::assertSame($scorer, $this->property($generator, 'scorer'));
        self::assertSame($calibrations, $this->property($generator, 'calibrationProvider'));
    }

    #[Test]
    public function itAcceptsAScoreEqualToTheThresholdOnTheOnlyAttempt(): void
    {
        $generator = new OtpGenerator(
            candidateGenerator: new SequenceCandidateGenerator(['0112']),
            maxAttempts: 1,
        );

        self::assertSame(
            '0112',
            $generator->generate(
                new OtpLength(4),
                ReadabilityPreset::Readable,
            )->value,
        );
    }

    #[Test]
    public function itRejectsCandidatesUntilOneMeetsThePresetThreshold(): void
    {
        $generator = new OtpGenerator(
            candidateGenerator: new SequenceCandidateGenerator([
                '583917',
                '144372',
                '121212',
            ]),
            maxAttempts: 3,
        );

        $code = $generator->generate(
            length: new OtpLength(6),
            preset: ReadabilityPreset::UberEasy,
        );

        self::assertSame(
            '121212',
            $code->value,
        );
    }

    #[Test]
    public function itReturnsTheFirstAcceptedCandidate(): void
    {
        $generator = new OtpGenerator(
            candidateGenerator: new SequenceCandidateGenerator([
                '121212',
                '112233',
            ]),
            maxAttempts: 2,
        );

        $code = $generator->generate(
            length: new OtpLength(6),
            preset: ReadabilityPreset::UberEasy,
        );

        self::assertSame(
            '121212',
            $code->value,
        );
    }

    #[Test]
    public function itFailsAfterMaximumAttempts(): void
    {
        $generator = new OtpGenerator(
            candidateGenerator: new SequenceCandidateGenerator([
                '583917',
                '583917',
                '583917',
            ]),
            maxAttempts: 3,
        );

        $this->expectException(
            OtpGenerationFailedException::class,
        );

        $generator->generate(
            length: new OtpLength(6),
            preset: ReadabilityPreset::UberEasy,
        );
    }

    #[Test]
    public function itRequiresAtLeastOneAttempt(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        new OtpGenerator(
            maxAttempts: 0,
        );
    }

    private function property(OtpGenerator $generator, string $name): mixed
    {
        $property = new \ReflectionProperty($generator, $name);

        return $property->getValue($generator);
    }
}
