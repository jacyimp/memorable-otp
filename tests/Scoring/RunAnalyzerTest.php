<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\Run;
use JacyImp\MemorableOtp\Scoring\RunAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RunAnalyzer::class)]
#[CoversClass(Run::class)]
final class RunAnalyzerTest extends TestCase
{
    /** @param list<array{string, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsRepeatedRuns(string $code, array $expected): void
    {
        $runs = (new RunAnalyzer())->analyze(new OtpCode($code));

        self::assertSame(
            $expected,
            array_map(
                static fn (Run $run): array => [$run->digit, $run->length],
                $runs,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{string, int}>}>
     */
    public static function codes(): iterable
    {
        yield 'none' => [
            '123456',
            [],
        ];

        yield 'single pair' => [
            '112345',
            [['1', 2]],
        ];

        yield 'multiple pairs' => [
            '112233',
            [
                ['1', 2],
                ['2', 2],
                ['3', 2],
            ],
        ];

        yield 'long run' => [
            '777777',
            [['7', 6]],
        ];

        yield 'mixed lengths' => [
            '111223',
            [
                ['1', 3],
                ['2', 2],
            ],
        ];

        yield 'separated same digit' => [
            '110011',
            [
                ['1', 2],
                ['0', 2],
                ['1', 2],
            ],
        ];
    }
}
