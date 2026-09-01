<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use InvalidArgumentException;
use JacyImp\MemorableOtp\Exception\OtpGenerationFailedException;
use JacyImp\MemorableOtp\OtpGenerator;
use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;
use JacyImp\MemorableOtp\Tests\Generation\SequenceCandidateGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtpGenerator::class)]
final class OtpGeneratorTest extends TestCase
{
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
}
