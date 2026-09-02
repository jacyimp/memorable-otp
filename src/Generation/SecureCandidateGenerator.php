<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Generation;

use Closure;
use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\OtpLength;

final readonly class SecureCandidateGenerator implements CandidateGenerator
{
    /** @var Closure(int, int): int */
    private Closure $randomInteger;

    /** @param null|Closure(int, int): int $randomInteger */
    public function __construct(?Closure $randomInteger = null)
    {
        $this->randomInteger = $randomInteger
            ?? static fn (int $minimum, int $maximum): int => random_int(
                $minimum,
                $maximum,
            );
    }

    public function generate(OtpLength $length): OtpCode
    {
        $value = '';

        for ($index = 0; $index < $length->value; ++$index) {
            $value .= ($this->randomInteger)(0, 9);
        }

        return new OtpCode($value);
    }
}
