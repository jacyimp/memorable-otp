<?php

declare(strict_types=1);

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityScorer;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;

require dirname(__DIR__) . '/vendor/autoload.php';

$codes = array_slice($argv, 1);

if ($codes === []) {
    fwrite(STDERR, "Usage: php tools/score.php <code> [code...]\n");

    exit(1);
}

$analyzer = new ReadabilityAnalyzer();

$descriptionCostCalculator = new MinimumDescriptionCostCalculator(
    new StructureCostCalculator(),
);

$transcriptionRiskCalculator = new TranscriptionRiskCalculator();

$scorer = new ReadabilityScorer(
    analyzer: $analyzer,
    descriptionCostCalculator: $descriptionCostCalculator,
    transcriptionRiskCalculator: $transcriptionRiskCalculator,
);

$rows = [];

foreach ($codes as $value) {
    $code = new OtpCode($value);
    $analysis = $analyzer->analyze($code);
    $score = $scorer->score($code);
    $description = $descriptionCostCalculator->describe($analysis);

    $rows[] = [
        'code' => $value,
        'score' => $score->value,
        'symbols' => $score->symbolSimplicity,
        'structure' => $score->structuralSimplicity,
        'coverage' => $score->structuralCoverage,
        'risk' => $score->transcriptionRisk,
        'description' => $description->cost,
        'unique' => $analysis->digitFrequency->uniqueDigits(),
        'explanation' => $description->explanation(),
    ];
}

usort(
    $rows,
    static fn (array $left, array $right): int => $right['score'] <=> $left['score'],
);

printf(
    "%-10s %8s %8s %10s %10s %8s %12s %7s\n",
    'CODE',
    'SCORE',
    'SYMBOL',
    'STRUCTURE',
    'COVERAGE',
    'RISK',
    'DESCRIPTION',
    'UNIQUE',
);

printf("%s\n", str_repeat('-', 90));

foreach ($rows as $row) {
    printf(
        "%-10s %8.4f %8.4f %10.4f %10.4f %8.4f %12.4f %7d\n",
        $row['code'],
        $row['score'],
        $row['symbols'],
        $row['structure'],
        $row['coverage'],
        $row['risk'],
        $row['description'],
        $row['unique'],
    );
}

printf("\nDescriptions:\n\n");

foreach ($rows as $row) {
    printf(
        "%-10s %s\n",
        $row['code'],
        $row['explanation'],
    );
}
