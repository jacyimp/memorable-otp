<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\ChunkSequence;
use JacyImp\MemorableOtp\Scoring\ChunkSequenceAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChunkSequenceAnalyzer::class)]
#[CoversClass(ChunkSequence::class)]
final class ChunkSequenceAnalyzerTest extends TestCase
{
    /** @param list<array{int, int, int, int, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsChunkSequences(
        string $code,
        array $expected,
    ): void {
        $result = (new ChunkSequenceAnalyzer())->analyze(
            new OtpCode($code),
        );

        self::assertSame(
            $expected,
            array_map(
                static fn (ChunkSequence $sequence): array => [
                    $sequence->start,
                    $sequence->step,
                    $sequence->chunkLength,
                    $sequence->chunks,
                    $sequence->offset,
                ],
                $result,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{int, int, int, int, int}>}>
     */
    public static function codes(): iterable
    {
        yield 'ascending chunks' => [
            '121314',
            [
                [12, 1, 2, 3, 0],
            ],
        ];

        yield 'another ascending chunk sequence' => [
            '212223',
            [
                [21, 1, 2, 3, 0],
            ],
        ];

        yield 'step ten' => [
            '102030',
            [
                [10, 10, 2, 3, 0],
            ],
        ];

        yield 'leading zeroes' => [
            '010203',
            [
                [1, 1, 2, 3, 0],
            ],
        ];

        yield 'four chunks' => [
            '12131415',
            [
                [12, 1, 2, 4, 0],
            ],
        ];

        yield 'sequence after prefix' => [
            '9121314',
            [
                [12, 1, 2, 3, 1],
            ],
        ];

        yield 'repetition is not a sequence' => [
            '121212',
            [],
        ];

        yield 'two chunks are insufficient' => [
            '1213',
            [],
        ];

        yield 'random' => [
            '583917',
            [],
        ];
    }
}
