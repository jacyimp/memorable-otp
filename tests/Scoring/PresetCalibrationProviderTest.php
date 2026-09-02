<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;
use JacyImp\MemorableOtp\Scoring\PresetCalibrationProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PresetCalibrationProvider::class)]
final class PresetCalibrationProviderTest extends TestCase
{
    #[Test]
    public function itReturnsExactCalibrationForSixDigits(): void
    {
        $calibration = (new PresetCalibrationProvider())->calibration(
            new OtpLength(6),
            ReadabilityPreset::UberEasy,
        );

        self::assertSame(
            0.311841510709,
            $calibration->threshold,
        );

        self::assertSame(
            0.094143,
            $calibration->retainedFraction,
        );

        self::assertTrue(
            $calibration->exact,
        );
    }

    #[Test]
    public function itMarksSampledCalibrationAsApproximate(): void
    {
        $calibration = (new PresetCalibrationProvider())->calibration(
            new OtpLength(7),
            ReadabilityPreset::UberEasy,
        );

        self::assertFalse(
            $calibration->exact,
        );
    }

    #[Test]
    public function stricterPresetsNeverHaveLowerThresholds(): void
    {
        $provider = new PresetCalibrationProvider();

        foreach (range(4, 10) as $length) {
            $otpLength = new OtpLength($length);

            $thresholds = [
                $provider->calibration(
                    $otpLength,
                    ReadabilityPreset::Readable,
                )->threshold,
                $provider->calibration(
                    $otpLength,
                    ReadabilityPreset::Easy,
                )->threshold,
                $provider->calibration(
                    $otpLength,
                    ReadabilityPreset::VeryEasy,
                )->threshold,
                $provider->calibration(
                    $otpLength,
                    ReadabilityPreset::SuperEasy,
                )->threshold,
                $provider->calibration(
                    $otpLength,
                    ReadabilityPreset::UberEasy,
                )->threshold,
            ];

            $sorted = $thresholds;
            sort($sorted);

            self::assertSame(
                $sorted,
                $thresholds,
            );
        }
    }
}
