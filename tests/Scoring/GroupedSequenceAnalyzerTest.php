<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\GroupedSequence;
use JacyImp\MemorableOtp\Scoring\GroupedSequenceAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupedSequenceAnalyzer::class)]
#[CoversClass(GroupedSequence::class)]
final class GroupedSequenceAnalyzerTest extends TestCase
{
    /** @param list<array{int, int, int, int, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsGroupedSequences(
        string $code,
        array $expected,
    ): void {
        $result = (new GroupedSequenceAnalyzer())->analyze(
            new OtpCode($code),
        );

        self::assertSame(
            $expected,
            array_map(
                static fn (GroupedSequence $sequence): array => [
                    $sequence->start,
                    $sequence->step,
                    $sequence->groupLength,
                    $sequence->groups,
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
        yield 'pairs ascending' => [
            '112233',
            [
                [1, 1, 2, 3, 0],
            ],
        ];

        yield 'triples ascending' => [
            '111222333',
            [
                [1, 1, 3, 3, 0],
            ],
        ];

        yield 'pairs descending' => [
            '998877',
            [
                [9, -1, 2, 3, 0],
            ],
        ];

        yield 'pairs with zero' => [
            '001122',
            [
                [0, 1, 2, 3, 0],
            ],
        ];

        yield 'four groups' => [
            '11223344',
            [
                [1, 1, 2, 4, 0],
            ],
        ];

        yield 'with prefix' => [
            '9112233',
            [
                [1, 1, 2, 3, 1],
            ],
        ];

        yield 'two groups are not enough' => [
            '111222',
            [],
        ];

        yield 'unequal group sizes' => [
            '11222333',
            [],
        ];

        yield 'random' => [
            '583917',
            [],
        ];
    }
}
