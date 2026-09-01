<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpCode;

final readonly class ReadabilityScorer
{
    private const SYMBOL_SIMPLICITY_WEIGHT = 0.4;

    private const SYMBOL_BASE_SUPPORT = 0.35;

    private const SYMBOL_STRUCTURE_SUPPORT = 0.65;

    private const TRANSCRIPTION_RISK_WEIGHT = 0.35;

    public function __construct(
        private ReadabilityAnalyzer $analyzer,
        private MinimumDescriptionCostCalculator $descriptionCostCalculator,
        private TranscriptionRiskCalculator $transcriptionRiskCalculator,
    ) {
    }

    public function score(OtpCode $code): ReadabilityScore
    {
        $analysis = $this->analyzer->analyze($code);
        $description = $this->descriptionCostCalculator->describe($analysis);

        $symbolSimplicity = 1.0
            - $analysis->digitFrequency->normalizedEntropy();

        $structuralSimplicity = 1.0 - min(
            1.0,
            $description->cost / $code->length(),
        );

        $structuralCoverage = $description->structuralCoverage(
            $code->length(),
        );

        $transcriptionRisk = $this->transcriptionRiskCalculator
            ->normalized($analysis);

        $remainingComplexity = 1.0 - $structuralSimplicity;

        $symbolSupport = self::SYMBOL_BASE_SUPPORT
            + (
                $structuralCoverage
                * self::SYMBOL_STRUCTURE_SUPPORT
            );

        $symbolBonus = $remainingComplexity
            * $symbolSimplicity
            * self::SYMBOL_SIMPLICITY_WEIGHT
            * $symbolSupport;

        $value = $structuralSimplicity
            + $symbolBonus
            - ($transcriptionRisk * self::TRANSCRIPTION_RISK_WEIGHT);

        return new ReadabilityScore(
            value: max(0.0, min(1.0, $value)),
            symbolSimplicity: $symbolSimplicity,
            structuralSimplicity: $structuralSimplicity,
            structuralCoverage: $structuralCoverage,
            transcriptionRisk: $transcriptionRisk,
        );
    }
}
