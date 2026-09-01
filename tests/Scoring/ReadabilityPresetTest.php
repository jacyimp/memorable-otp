<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use JacyImp\MemorableOtp\ReadabilityPreset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadabilityPreset::class)]
final class ReadabilityPresetTest extends TestCase
{
    #[Test]
    #[DataProvider('acceptanceRates')]
    public function itExposesItsTargetAcceptanceRate(
        ReadabilityPreset $preset,
        float $expected,
    ): void {
        self::assertSame(
            $expected,
            $preset->targetAcceptanceRate(),
        );
    }

    /**
     * @return iterable<string, array{ReadabilityPreset, float}>
     */
    public static function acceptanceRates(): iterable
    {
        yield 'readable' => [
            ReadabilityPreset::Readable,
            0.50,
        ];

        yield 'easy' => [
            ReadabilityPreset::Easy,
            0.30,
        ];

        yield 'very easy' => [
            ReadabilityPreset::VeryEasy,
            0.20,
        ];

        yield 'super easy' => [
            ReadabilityPreset::SuperEasy,
            0.15,
        ];

        yield 'uber easy' => [
            ReadabilityPreset::UberEasy,
            0.10,
        ];
    }

    #[Test]
    public function itCalculatesTargetEntropyLoss(): void
    {
        self::assertEqualsWithDelta(
            1.0,
            ReadabilityPreset::Readable->targetEntropyLossBits(),
            0.0001,
        );

        self::assertEqualsWithDelta(
            3.321928,
            ReadabilityPreset::UberEasy->targetEntropyLossBits(),
            0.0001,
        );
    }
}
