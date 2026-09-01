<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use InvalidArgumentException;
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemorableOtp::class)]
final class MemorableOtpTest extends TestCase
{
    #[Test]
    #[DataProvider('presetMethods')]
    public function itGeneratesNumericCodesWithRequestedLength(
        string $method,
    ): void {
        $code = match ($method) {
            'readable' => MemorableOtp::readable(7),
            'easy' => MemorableOtp::easy(7),
            'veryEasy' => MemorableOtp::veryEasy(7),
            'superEasy' => MemorableOtp::superEasy(7),
            'uberEasy' => MemorableOtp::uberEasy(7),
            default => throw new InvalidArgumentException(
                'Unknown preset method.',
            ),
        };

        self::assertMatchesRegularExpression(
            '/^\d{7}$/',
            $code,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function presetMethods(): iterable
    {
        yield 'readable' => ['readable'];
        yield 'easy' => ['easy'];
        yield 'very easy' => ['veryEasy'];
        yield 'super easy' => ['superEasy'];
        yield 'uber easy' => ['uberEasy'];
    }

    #[Test]
    public function itGeneratesUsingExplicitPreset(): void
    {
        $code = MemorableOtp::generate(
            length: 6,
            preset: ReadabilityPreset::VeryEasy,
        );

        self::assertMatchesRegularExpression(
            '/^\d{6}$/',
            $code,
        );
    }

    #[Test]
    public function itDefaultsToSixDigits(): void
    {
        self::assertMatchesRegularExpression(
            '/^\d{6}$/',
            MemorableOtp::readable(),
        );
    }

    #[Test]
    public function itRejectsUnsupportedLengths(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
        );

        MemorableOtp::readable(3);
    }
}
