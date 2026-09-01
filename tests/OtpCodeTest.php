<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use InvalidArgumentException;
use JacyImp\MemorableOtp\OtpCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtpCode::class)]
final class OtpCodeTest extends TestCase
{
    #[Test]
    public function itPreservesTheCode(): void
    {
        $code = new OtpCode('001234');

        self::assertSame('001234', $code->value);
        self::assertSame('001234', (string) $code);
        self::assertSame(6, $code->length());
    }

    #[Test]
    #[DataProvider('invalidCodes')]
    public function itRejectsInvalidCodes(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OtpCode($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCodes(): iterable
    {
        yield 'empty' => [''];
        yield 'letters' => ['123abc'];
        yield 'spaces' => ['123 456'];
        yield 'negative' => ['-123456'];
        yield 'decimal' => ['123.456'];
    }
}
