<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

use InvalidArgumentException;

final readonly class OtpCode
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '' || !ctype_digit($value)) {
            throw new InvalidArgumentException('OTP code must contain digits only.');
        }
    }

    public function length(): int
    {
        return strlen($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
