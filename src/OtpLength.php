<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

use InvalidArgumentException;

final readonly class OtpLength
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 4 || $value > 10) {
            throw new InvalidArgumentException('OTP length must be between 4 and 10 digits.');
        }
    }
}
