<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Scoring;

use JacyImp\MemorableOtp\Scoring\Description;
use JacyImp\MemorableOtp\Scoring\DescriptionSegment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Description::class)]
#[CoversClass(DescriptionSegment::class)]
final class DescriptionTest extends TestCase
{
    #[Test]
    public function itCalculatesWeightedStructuralCoverage(): void
    {
        $description = new Description(
            cost: 4.0,
            segments: [
                new DescriptionSegment(
                    label: 'Round(100)',
                    offset: 0,
                    length: 3,
                    cost: 2.25,
                    structuralWeight: 2 / 3,
                ),
                new DescriptionSegment(
                    label: 'Round(20)',
                    offset: 3,
                    length: 2,
                    cost: 1.5,
                    structuralWeight: 1 / 2,
                ),
            ],
        );

        self::assertEqualsWithDelta(
            3 / 5,
            $description->structuralCoverage(5),
            0.0001,
        );
    }

    #[Test]
    public function itDoesNotCountLiteralDigitsAsStructure(): void
    {
        $description = new Description(
            cost: 6.0,
            segments: [
                new DescriptionSegment(
                    label: 'Literal(583917)',
                    offset: 0,
                    length: 6,
                    cost: 6.0,
                    structuralWeight: 0.0,
                ),
            ],
        );

        self::assertSame(
            0.0,
            $description->structuralCoverage(6),
        );
    }

    #[Test]
    public function itCountsStrongPatternsAsFullyStructured(): void
    {
        $description = new Description(
            cost: 2.5,
            segments: [
                new DescriptionSegment(
                    label: 'Repeat(12×3)',
                    offset: 0,
                    length: 6,
                    cost: 2.5,
                ),
            ],
        );

        self::assertSame(
            1.0,
            $description->structuralCoverage(6),
        );
    }
}
