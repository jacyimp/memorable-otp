<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp;

use InvalidArgumentException;
use JacyImp\MemorableOtp\Exception\OtpGenerationFailedException;
use JacyImp\MemorableOtp\Generation\CandidateGenerator;
use JacyImp\MemorableOtp\Generation\SecureCandidateGenerator;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\PresetCalibrationProvider;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityScorer;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;

final readonly class OtpGenerator
{
    private CandidateGenerator $candidateGenerator;

    private ReadabilityScorer $scorer;

    private PresetCalibrationProvider $calibrationProvider;

    public function __construct(
        ?CandidateGenerator $candidateGenerator = null,
        ?ReadabilityScorer $scorer = null,
        ?PresetCalibrationProvider $calibrationProvider = null,
        private int $maxAttempts = 1000,
    ) {
        if ($maxAttempts < 1) {
            throw new InvalidArgumentException(
                'Maximum attempts must be at least 1.',
            );
        }

        $this->candidateGenerator = $candidateGenerator
            ?? new SecureCandidateGenerator();

        $this->scorer = $scorer
            ?? new ReadabilityScorer(
                analyzer: new ReadabilityAnalyzer(),
                descriptionCostCalculator: new MinimumDescriptionCostCalculator(
                    new StructureCostCalculator(),
                ),
                transcriptionRiskCalculator: new TranscriptionRiskCalculator(),
            );

        $this->calibrationProvider = $calibrationProvider
            ?? new PresetCalibrationProvider();
    }

    public function generate(
        OtpLength $length,
        ReadabilityPreset $preset,
    ): OtpCode {
        $calibration = $this->calibrationProvider->calibration(
            length: $length,
            preset: $preset,
        );

        for ($attempt = 1; $attempt <= $this->maxAttempts; ++$attempt) {
            $candidate = $this->candidateGenerator->generate($length);
            $score = $this->scorer->score($candidate);

            if ($score->value >= $calibration->threshold) {
                return $candidate;
            }
        }

        throw OtpGenerationFailedException::afterMaximumAttempts(
            length: $length,
            preset: $preset,
            attempts: $this->maxAttempts,
        );
    }
}
