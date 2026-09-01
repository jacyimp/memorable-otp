<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\Scoring\ChunkSequence;
use JacyImp\MemorableOtp\Scoring\Mirror;
use JacyImp\MemorableOtp\Scoring\RepeatedChunk;
use JacyImp\MemorableOtp\Scoring\Run;
use JacyImp\MemorableOtp\Scoring\Sequence;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructureCostCalculator::class)]
final class StructureCostCalculatorTest extends TestCase
{
    #[Test]
    public function itMakesSimpleSequencesCheaperThanComplexSequences(): void
    {
        $calculator = new StructureCostCalculator();

        $simple = new Sequence(
            start: 1,
            step: 1,
            length: 6,
            offset: 0,
        );

        $complex = new Sequence(
            start: 1,
            step: 3,
            length: 3,
            offset: 0,
        );

        self::assertLessThan(
            $calculator->forSequence($complex),
            $calculator->forSequence($simple),
        );
    }

    #[Test]
    public function itMakesShortRepeatedChunksCheapToDescribe(): void
    {
        $calculator = new StructureCostCalculator();

        $chunk = new RepeatedChunk(
            chunk: '12',
            repetitions: 3,
            offset: 0,
        );

        self::assertLessThan(
            6.0,
            $calculator->forRepeatedChunk($chunk),
        );
    }

    #[Test]
    public function itMakesRunsCheapToDescribe(): void
    {
        $calculator = new StructureCostCalculator();

        $run = new Run(
            digit: '7',
            length: 6,
            offset: 0,
        );

        self::assertLessThan(
            6.0,
            $calculator->forRun($run),
        );
    }

    #[Test]
    public function itMakesMirrorsCheaperThanRememberingEveryDigit(): void
    {
        $calculator = new StructureCostCalculator();

        $mirror = new Mirror(
            value: '1234321',
            offset: 0,
        );

        self::assertLessThan(
            7.0,
            $calculator->forMirror($mirror),
        );
    }

    #[Test]
    public function itTreatsDecimalScaledStepsAsEquallySimple(): void
    {
        $calculator = new StructureCostCalculator();

        $stepOne = new ChunkSequence(
            start: 10,
            step: 1,
            chunkLength: 2,
            chunks: 3,
            offset: 0,
        );

        $stepTen = new ChunkSequence(
            start: 10,
            step: 10,
            chunkLength: 2,
            chunks: 3,
            offset: 0,
        );

        self::assertSame(
            $calculator->forChunkSequence($stepOne),
            $calculator->forChunkSequence($stepTen),
        );
    }

    #[Test]
    public function itTreatsSimpleDecimalStepsAsCheaperThanLiteralDigits(): void
    {
        $calculator = new StructureCostCalculator();

        $sequence = new ChunkSequence(
            start: 10,
            step: 20,
            chunkLength: 2,
            chunks: 3,
            offset: 0,
        );

        self::assertLessThan(
            6.0,
            $calculator->forChunkSequence($sequence),
        );
    }

    #[Test]
    public function itDoesNotRewardArbitraryChunkArithmetic(): void
    {
        $calculator = new StructureCostCalculator();

        $sequence = new ChunkSequence(
            start: 14,
            step: 29,
            chunkLength: 2,
            chunks: 3,
            offset: 0,
        );

        self::assertSame(
            6.0,
            $calculator->forChunkSequence($sequence),
        );
    }

    #[Test]
    public function itRecognizesPowersOfTenAsSimpleDecimalScaling(): void
    {
        $calculator = new StructureCostCalculator();

        $stepOne = new ChunkSequence(
            start: 10,
            step: 1,
            chunkLength: 2,
            chunks: 3,
            offset: 0,
        );

        $stepTen = new ChunkSequence(
            start: 10,
            step: 10,
            chunkLength: 2,
            chunks: 3,
            offset: 0,
        );

        $stepHundred = new ChunkSequence(
            start: 100,
            step: 100,
            chunkLength: 3,
            chunks: 3,
            offset: 0,
        );

        self::assertSame(
            $calculator->forChunkSequence($stepOne),
            $calculator->forChunkSequence($stepTen),
        );

        self::assertLessThan(
            9.0,
            $calculator->forChunkSequence($stepHundred),
        );
    }
}
