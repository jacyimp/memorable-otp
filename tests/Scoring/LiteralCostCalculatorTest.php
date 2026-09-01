<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\Scoring\LiteralCostCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LiteralCostCalculator::class)]
final class LiteralCostCalculatorTest extends TestCase
{
    #[Test]
    #[DataProvider('ordinaryLiterals')]
    public function itUsesDigitLengthForOrdinaryLiterals(
        string $literal,
        float $expected,
    ): void {
        self::assertSame(
            $expected,
            (new LiteralCostCalculator())->calculate($literal),
        );
    }

    /**
     * @return iterable<string, array{string, float}>
     */
    public static function ordinaryLiterals(): iterable
    {
        yield 'single digit' => ['5', 1.0];
        yield 'two digits' => ['29', 2.0];
        yield 'three digits' => ['583', 3.0];
        yield 'leading zero' => ['017', 3.0];
    }

    #[Test]
    #[DataProvider('decimalLiterals')]
    public function itMakesRoundDecimalLiteralsCheaper(
        string $literal,
        float $expected,
    ): void {
        self::assertSame(
            $expected,
            (new LiteralCostCalculator())->calculate($literal),
        );
    }

    /**
     * @return iterable<string, array{string, float}>
     */
    public static function decimalLiterals(): iterable
    {
        yield 'twenty' => ['20', 1.25];
        yield 'one hundred' => ['100', 1.5];
        yield 'two hundred' => ['200', 1.5];
        yield 'three thousand' => ['3000', 1.5];
        yield 'one hundred twenty' => ['120', 2.25];
        yield 'twelve hundred' => ['1200', 2.5];
    }

    #[Test]
    public function itDoesNotTreatAllZeroesAsADecimalLiteral(): void
    {
        self::assertSame(
            4.0,
            (new LiteralCostCalculator())->calculate('0000'),
        );
    }
}
