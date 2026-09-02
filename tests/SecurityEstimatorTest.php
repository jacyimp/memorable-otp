<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;
use JacyImp\MemorableOtp\SecurityEstimate;
use JacyImp\MemorableOtp\SecurityEstimator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecurityEstimator::class)]
#[CoversClass(SecurityEstimate::class)]
final class SecurityEstimatorTest extends TestCase
{
    #[Test]
    public function itEstimatesAcceptedSearchSpace(): void
    {
        $estimate = (new SecurityEstimator())->estimate(
            new OtpLength(6),
            ReadabilityPreset::UberEasy,
        );

        self::assertSame(
            1_000_000.0,
            $estimate->rawSearchSpace(),
        );

        self::assertEqualsWithDelta(
            94_143.0,
            $estimate->acceptedSearchSpace(),
            0.001,
        );

        self::assertTrue(
            $estimate->exact,
        );
    }

    #[Test]
    public function sevenDigitUberEasyIsApproximatelySixRandomDigits(): void
    {
        $estimate = (new SecurityEstimator())->estimate(
            new OtpLength(7),
            ReadabilityPreset::UberEasy,
        );

        self::assertEqualsWithDelta(
            978_740.0,
            $estimate->acceptedSearchSpace(),
            1.0,
        );

        self::assertFalse(
            $estimate->exact,
        );
    }

    #[Test]
    public function itCalculatesEntropyLoss(): void
    {
        $estimate = (new SecurityEstimator())->estimate(
            new OtpLength(6),
            ReadabilityPreset::UberEasy,
        );

        self::assertEqualsWithDelta(
            -log(0.094143, 2),
            $estimate->entropyLossBits(),
            0.000001,
        );
    }
}
