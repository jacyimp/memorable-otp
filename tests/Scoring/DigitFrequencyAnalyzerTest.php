<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyProfile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DigitFrequencyAnalyzer::class)]
#[CoversClass(DigitFrequencyProfile::class)]
final class DigitFrequencyAnalyzerTest extends TestCase
{
    /** @param array<int, int> $expectedFrequencies */
    #[Test]
    #[DataProvider('codes')]
    public function itAnalyzesDigitFrequency(
        string $code,
        array $expectedFrequencies,
        int $expectedUniqueDigits,
        int $expectedHighestFrequency,
    ): void {
        $profile = (new DigitFrequencyAnalyzer())->analyze(
            new OtpCode($code),
        );

        self::assertSame($expectedFrequencies, $profile->frequencies);
        self::assertSame(strlen($code), $profile->length());
        self::assertSame($expectedUniqueDigits, $profile->uniqueDigits());
        self::assertSame($expectedHighestFrequency, $profile->highestFrequency());
    }

    #[Test]
    public function itCalculatesMaximumEntropyForUniqueDigits(): void
    {
        $profile = (new DigitFrequencyAnalyzer())->analyze(
            new OtpCode('123456'),
        );

        self::assertEqualsWithDelta(
            1.0,
            $profile->normalizedEntropy(),
            0.0001,
        );
    }

    #[Test]
    public function itCalculatesZeroEntropyForOneRepeatedDigit(): void
    {
        $profile = (new DigitFrequencyAnalyzer())->analyze(
            new OtpCode('777777'),
        );

        self::assertSame(0.0, $profile->entropy());
        self::assertSame(0.0, $profile->normalizedEntropy());
    }

    #[Test]
    public function itMeasuresLowerEntropyForFewerDistinctDigits(): void
    {
        $analyzer = new DigitFrequencyAnalyzer();

        $random = $analyzer->analyze(new OtpCode('123456'));
        $pairs = $analyzer->analyze(new OtpCode('112233'));
        $alternating = $analyzer->analyze(new OtpCode('121212'));

        self::assertGreaterThan(
            $pairs->normalizedEntropy(),
            $random->normalizedEntropy(),
        );

        self::assertGreaterThan(
            $alternating->normalizedEntropy(),
            $pairs->normalizedEntropy(),
        );
    }

    /**
     * @return iterable<string, array{string, array<int, int>, int, int}>
     */
    public static function codes(): iterable
    {
        yield 'all unique' => [
            '123456',
            [
                '1' => 1,
                '2' => 1,
                '3' => 1,
                '4' => 1,
                '5' => 1,
                '6' => 1,
            ],
            6,
            1,
        ];

        yield 'balanced pairs' => [
            '112233',
            [
                '1' => 2,
                '2' => 2,
                '3' => 2,
            ],
            3,
            2,
        ];

        yield 'uneven distribution' => [
            '111223',
            [
                '1' => 3,
                '2' => 2,
                '3' => 1,
            ],
            3,
            3,
        ];

        yield 'single dominant digit' => [
            '111123',
            [
                '1' => 4,
                '2' => 1,
                '3' => 1,
            ],
            3,
            4,
        ];

        yield 'single digit' => [
            '777777',
            [
                '7' => 6,
            ],
            1,
            6,
        ];

        yield 'leading zeroes' => [
            '001122',
            [
                '0' => 2,
                '1' => 2,
                '2' => 2,
            ],
            3,
            2,
        ];
    }
}
