<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class LiteralCostCalculator
{
    public function calculate(string $literal): float
    {
        if ($literal === '' || $literal[0] === '0') {
            return (float) strlen($literal);
        }

        $trailingZeroes = strlen($literal) - strlen(rtrim($literal, '0'));

        return strlen($literal) - $trailingZeroes
            + min(0.5, $trailingZeroes * 0.25);
    }
}
