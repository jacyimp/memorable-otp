<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\PeriodicPattern;
use JacyImp\MemorableOtp\Scoring\PeriodicPatternAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PeriodicPatternAnalyzer::class)]
#[CoversClass(PeriodicPattern::class)]
final class PeriodicPatternAnalyzerTest extends TestCase
{
    /** @param list<array{string, int, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsPeriodicPatterns(
        string $code,
        array $expected,
    ): void {
        $patterns = (new PeriodicPatternAnalyzer())->analyze(
            new OtpCode($code),
        );

        self::assertSame(
            $expected,
            array_map(
                static fn (PeriodicPattern $pattern): array => [
                    $pattern->unit,
                    $pattern->length,
                    $pattern->offset,
                ],
                $patterns,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{string, int, int}>}>
     */
    public static function codes(): iterable
    {
        yield 'alternating odd length' => [
            '81818',
            [
                ['81', 5, 0],
            ],
        ];

        yield 'seven digit alternation' => [
            '1212121',
            [
                ['12', 7, 0],
            ],
        ];

        yield 'zero alternation' => [
            '70707',
            [
                ['70', 5, 0],
            ],
        ];

        yield 'periodic pattern after prefix' => [
            '912121',
            [
                ['12', 5, 1],
            ],
        ];

        yield 'complete repetition belongs to repeated chunks' => [
            '121212',
            [],
        ];

        yield 'only two complete units are insufficient' => [
            '12121',
            [
                ['12', 5, 0],
            ],
        ];

        yield 'random' => [
            '59381',
            [],
        ];
    }
}
