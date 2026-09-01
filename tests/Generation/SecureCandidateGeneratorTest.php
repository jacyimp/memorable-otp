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
