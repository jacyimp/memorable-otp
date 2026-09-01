<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

final readonly class MemorableOtp
{
    public static function generate(
        int $length = 6,
        ReadabilityPreset $preset = ReadabilityPreset::Readable,
    ): string {
        return (new OtpGenerator())->generate(
            length: new OtpLength($length),
            preset: $preset,
        )->value;
    }

    public static function readable(int $length = 6): string
    {
        return self::generate(
            length: $length,
            preset: ReadabilityPreset::Readable,
        );
    }

    public static function easy(int $length = 6): string
    {
        return self::generate(
            length: $length,
            preset: ReadabilityPreset::Easy,
        );
    }

    public static function veryEasy(int $length = 6): string
    {
        return self::generate(
            length: $length,
            preset: ReadabilityPreset::VeryEasy,
        );
    }

    public static function superEasy(int $length = 6): string
    {
        return self::generate(
            length: $length,
            preset: ReadabilityPreset::SuperEasy,
        );
    }

    public static function uberEasy(int $length = 6): string
    {
        return self::generate(
            length: $length,
            preset: ReadabilityPreset::UberEasy,
        );
    }

    public static function security(
        int $length = 6,
        ReadabilityPreset $preset = ReadabilityPreset::Readable,
    ): SecurityEstimate {
        return (new SecurityEstimator())->estimate(
            length: new OtpLength($length),
            preset: $preset,
        );
    }
}
