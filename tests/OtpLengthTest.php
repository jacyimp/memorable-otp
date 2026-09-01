<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use InvalidArgumentException;
use JacyImp\MemorableOtp\OtpLength;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OtpLength::class)]
final class OtpLengthTest extends TestCase
{
    #[Test]
    #[DataProvider('validLengths')]
    public function itAcceptsValidLengths(int $length): void
    {
        $otpLength = new OtpLength($length);

        self::assertSame($length, $otpLength->value);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function validLengths(): iterable
    {
        yield 'minimum' => [4];
        yield 'six digits' => [6];
        yield 'seven digits' => [7];
        yield 'maximum' => [10];
    }

    #[Test]
    #[DataProvider('invalidLengths')]
    public function itRejectsInvalidLengths(int $length): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OtpLength($length);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidLengths(): iterable
    {
        yield 'zero' => [0];
        yield 'below minimum' => [3];
        yield 'above maximum' => [11];
    }
}
