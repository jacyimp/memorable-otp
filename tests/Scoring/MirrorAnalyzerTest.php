<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\Mirror;
use JacyImp\MemorableOtp\Scoring\MirrorAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MirrorAnalyzer::class)]
#[CoversClass(Mirror::class)]
final class MirrorAnalyzerTest extends TestCase
{
    /** @param list<array{string, int}> $expected */
    #[Test]
    #[DataProvider('codes')]
    public function itFindsMirrors(string $code, array $expected): void
    {
        $result = (new MirrorAnalyzer())->analyze(new OtpCode($code));

        self::assertSame(
            $expected,
            array_map(
                static fn (Mirror $mirror): array => [
                    $mirror->value,
                    $mirror->offset,
                ],
                $result,
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

        yield 'odd mirror' => [
            '1234321',
            [
                ['1234321', 0],
            ],
        ];

        yield 'even mirror' => [
            '123321',
            [
                ['123321', 0],
            ],
        ];

        yield 'short mirror' => [
            '121456',
            [
                ['121', 0],
            ],
        ];

        yield 'mirror after prefix' => [
            '9123321',
            [
                ['123321', 1],
            ],
        ];

        yield 'mirror before suffix' => [
            '122178',
            [
                ['1221', 0],
            ],
        ];

        yield 'zero mirror' => [
            '770077',
            [
                ['770077', 0],
            ],
        ];

        yield 'contained mirrors collapse to maximal mirror' => [
            '123454321',
            [
                ['123454321', 0],
            ],
        ];
    }
}
