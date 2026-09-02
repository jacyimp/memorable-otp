<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Generation;

use JacyImp\MemorableOtp\Generation\SecureCandidateGenerator;
use JacyImp\MemorableOtp\OtpLength;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecureCandidateGenerator::class)]
final class SecureCandidateGeneratorTest extends TestCase
{
    #[Test]
    public function itRequestsEveryDecimalDigitFromTheSecureSource(): void
    {
        $calls = [];
        $digits = [0, 9, 0, 9];
        $generator = new SecureCandidateGenerator(
            static function (int $minimum, int $maximum) use (&$calls, &$digits): int {
                $calls[] = [$minimum, $maximum];

                return array_shift($digits) ?? 0;
            },
        );

        self::assertSame('0909', $generator->generate(new OtpLength(4))->value);
        self::assertSame(
            [[0, 9], [0, 9], [0, 9], [0, 9]],
            $calls,
        );
    }

    #[Test]
    public function itGeneratesCodeWithRequestedLength(): void
    {
        $generator = new SecureCandidateGenerator();

        $code = $generator->generate(
            new OtpLength(6),
        );

        self::assertSame(
            6,
            $code->length(),
        );
    }

    #[Test]
    public function itGeneratesOnlyDigits(): void
    {
        $generator = new SecureCandidateGenerator();

        $code = $generator->generate(
            new OtpLength(10),
        );

        self::assertMatchesRegularExpression(
            '/^\d{10}$/',
            $code->value,
        );
    }
}
