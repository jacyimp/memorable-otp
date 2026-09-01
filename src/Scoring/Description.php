<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class Description
{
    /**
     * @param list<DescriptionSegment> $segments
     */
    public function __construct(
        public float $cost,
        public array $segments,
    ) {
    }

    public function explanation(): string
    {
        return implode(
            ' | ',
            array_map(
                static fn (DescriptionSegment $segment): string => $segment->label,
                $this->segments,
            ),
        );
    }

    public function structuralCoverage(int $codeLength): float
    {
        if ($codeLength === 0) {
            return 0.0;
        }

        $structuredLength = 0.0;

        foreach ($this->segments as $segment) {
            $structuredLength += $segment->length
                * $segment->structuralWeight;
        }

        return min(
            1.0,
            $structuredLength / $codeLength,
        );
    }
}
