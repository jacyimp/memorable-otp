<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Generation;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\OtpLength;

final readonly class SecureCandidateGenerator implements CandidateGenerator
{
    public function generate(OtpLength $length): OtpCode
    {
        $value = '';

        for ($index = 0; $index < $length->value; ++$index) {
            $value .= (string) random_int(0, 9);
        }

        return new OtpCode($value);
    }
}
