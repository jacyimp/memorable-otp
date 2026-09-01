<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

use JacyImp\MemorableOtp\Scoring\PresetCalibrationProvider;

final readonly class SecurityEstimator
{
    public function __construct(
        private PresetCalibrationProvider $calibrationProvider = new PresetCalibrationProvider(),
    ) {
    }

    public function estimate(
        OtpLength $length,
        ReadabilityPreset $preset,
    ): SecurityEstimate {
        $calibration = $this->calibrationProvider->calibration(
            length: $length,
            preset: $preset,
        );

        return new SecurityEstimate(
            length: $length->value,
            preset: $preset,
            retainedFraction: $calibration->retainedFraction,
            exact: $calibration->exact,
        );
    }
}
