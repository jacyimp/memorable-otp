<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\RepeatedChunk;
use JacyImp\MemorableOtp\Scoring\RepeatedChunkAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RepeatedChunkAnalyzer::class)]
#[CoversClass(RepeatedChunk::class)]
final class RepeatedChunkAnalyzerTest extends TestCase
{
    /** @param list<array{string, int, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsRepeatedChunks(string $code, array $expected): void
    {
        $result = (new RepeatedChunkAnalyzer())->analyze(new OtpCode($code));

        self::assertSame(
            $expected,
            array_map(
                static fn (RepeatedChunk $chunk): array => [
                    $chunk->chunk,
                    $chunk->repetitions,
                    $chunk->offset,
                ],
                $result,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{string, int, int}>}>
     */
    public static function codes(): iterable
    {
        yield 'none' => [
            '123456',
            [],
        ];

        yield 'pair repeated' => [
            '121212',
            [
                ['12', 3, 0],
            ],
        ];

        yield 'triplet repeated' => [
            '123123',
            [
                ['123', 2, 0],
            ],
        ];

        yield 'repetition with trailing digit' => [
            '1201207',
            [
                ['120', 2, 0],
            ],
        ];

        yield 'repetition after prefix' => [
            '9121212',
            [
                ['12', 3, 1],
            ],
        ];
    }
}
