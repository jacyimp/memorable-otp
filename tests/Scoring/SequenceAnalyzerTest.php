<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\Sequence;
use JacyImp\MemorableOtp\Scoring\SequenceAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SequenceAnalyzer::class)]
#[CoversClass(Sequence::class)]
final class SequenceAnalyzerTest extends TestCase
{
    /** @param list<array{int, int, int, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsSequences(string $code, array $expected): void
    {
        $result = (new SequenceAnalyzer())->analyze(new OtpCode($code));

        self::assertSame(
            $expected,
            array_map(
                static fn (Sequence $sequence): array => [
                    $sequence->start,
                    $sequence->step,
                    $sequence->length,
                    $sequence->offset,
                ],
                $result,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{int, int, int, int}>}>
     */
    public static function codes(): iterable
    {
        yield 'none' => [
            '183605',
            [],
        ];

        yield 'ascending' => [
            '123456',
            [
                [1, 1, 6, 0],
            ],
        ];

        yield 'descending' => [
            '654321',
            [
                [6, -1, 6, 0],
            ],
        ];

        yield 'step two' => [
            '2468',
            [
                [2, 2, 4, 0],
            ],
        ];

        yield 'descending step two' => [
            '8642',
            [
                [8, -2, 4, 0],
            ],
        ];

        yield 'sequence with prefix' => [
            '912345',
            [
                [1, 1, 5, 1],
            ],
        ];

        yield 'sequence with suffix' => [
            '123457',
            [
                [1, 1, 5, 0],
            ],
        ];

        yield 'multiple sequences' => [
            '123987',
            [
                [1, 1, 3, 0],
                [9, -1, 3, 3],
            ],
        ];

        yield 'repeated digits are not sequences' => [
            '111222',
            [],
        ];
    }
}
