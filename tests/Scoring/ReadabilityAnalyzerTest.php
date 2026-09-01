<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\DigitFrequencyAnalyzer;
use JacyImp\MemorableOtp\Scoring\MirrorAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalysis;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\RepeatedChunkAnalyzer;
use JacyImp\MemorableOtp\Scoring\RunAnalyzer;
use JacyImp\MemorableOtp\Scoring\SequenceAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadabilityAnalyzer::class)]
#[CoversClass(ReadabilityAnalysis::class)]
final class ReadabilityAnalyzerTest extends TestCase
{
    #[Test]
    public function itAnalyzesAReadableCode(): void
    {
        $analysis = $this->analyzer()->analyze(
            new OtpCode('1122334'),
        );

        self::assertSame('1122334', $analysis->code->value);

        self::assertSame(
            [
                '1' => 2,
                '2' => 2,
                '3' => 2,
                '4' => 1,
            ],
            $analysis->digitFrequency->frequencies,
        );

        self::assertCount(3, $analysis->runs);
        self::assertSame('1', $analysis->runs[0]->digit);
        self::assertSame(2, $analysis->runs[0]->length);

        self::assertSame([], $analysis->repeatedChunks);
        self::assertSame([], $analysis->sequences);
        self::assertSame([], $analysis->mirrors);
    }

    #[Test]
    public function itCollectsDifferentKindsOfStructure(): void
    {
        $analysis = $this->analyzer()->analyze(
            new OtpCode('1212121'),
        );

        self::assertSame(2, $analysis->digitFrequency->uniqueDigits());
        self::assertSame([], $analysis->runs);

        self::assertNotEmpty($analysis->repeatedChunks);
        self::assertSame('12', $analysis->repeatedChunks[0]->chunk);
        self::assertSame(3, $analysis->repeatedChunks[0]->repetitions);
    }

    private function analyzer(): ReadabilityAnalyzer
    {
        return new ReadabilityAnalyzer(
            digitFrequencyAnalyzer: new DigitFrequencyAnalyzer(),
            runAnalyzer: new RunAnalyzer(),
            repeatedChunkAnalyzer: new RepeatedChunkAnalyzer(),
            sequenceAnalyzer: new SequenceAnalyzer(),
            mirrorAnalyzer: new MirrorAnalyzer(),
        );
    }
}
