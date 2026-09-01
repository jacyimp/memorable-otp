<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Exception;

use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;
use RuntimeException;

final class OtpGenerationFailedException extends RuntimeException
{
    public static function afterMaximumAttempts(
        OtpLength $length,
        ReadabilityPreset $preset,
        int $attempts,
    ): self {
        return new self(
            sprintf(
                'Unable to generate a %d-digit %s OTP after %d attempts.',
                $length->value,
                $preset->value,
                $attempts,
            ),
        );
    }
}
