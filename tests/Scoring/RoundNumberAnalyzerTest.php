<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\RoundNumber;
use JacyImp\MemorableOtp\Scoring\RoundNumberAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundNumberAnalyzer::class)]
#[CoversClass(RoundNumber::class)]
final class RoundNumberAnalyzerTest extends TestCase
{
    /** @param list<array{string, int, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsRoundNumbers(
        string $code,
        array $expected,
    ): void {
        $numbers = (new RoundNumberAnalyzer())->analyze(
            new OtpCode($code),
        );

        self::assertSame(
            $expected,
            array_map(
                static fn (RoundNumber $number): array => [
                    $number->value,
                    $number->offset,
                    $number->trailingZeroes,
                ],
                $numbers,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{string, int, int}>}>
     */
    public static function codes(): iterable
    {
        yield 'twenty' => [
            '20',
            [
                ['20', 0, 1],
            ],
        ];

        yield 'one hundred' => [
            '100',
            [
                ['10', 0, 1],
                ['100', 0, 2],
            ],
        ];

        yield 'round number inside code' => [
            '91002',
            [
                ['10', 1, 1],
                ['100', 1, 2],
            ],
        ];

        yield 'leading zero is not round number' => [
            '050',
            [],
        ];

        yield 'all zeroes are not round numbers' => [
            '000',
            [],
        ];

        yield 'ordinary number' => [
            '583',
            [],
        ];
    }
}
