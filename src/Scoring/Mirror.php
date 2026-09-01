<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class Mirror
{
    public function __construct(
        public string $value,
        public int $offset,
    ) {
    }

    public function length(): int
    {
        return strlen($this->value);
    }
}
