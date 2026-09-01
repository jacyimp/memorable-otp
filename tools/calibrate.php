<?php

declare(strict_types=1);

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityScorer;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;
use Random\Engine\Mt19937;
use Random\Randomizer;

require dirname(__DIR__) . '/vendor/autoload.php';

const EXACT_MAX_LENGTH = 6;
const DEFAULT_SAMPLES = 500_000;
const DEFAULT_SEED = 20260901;
const EXAMPLES_PER_SCORE = 3;

$presetBudgets = [
    'readable' => 0.50,
    'easy' => 0.30,
    'veryEasy' => 0.20,
    'superEasy' => 0.15,
    'uberEasy' => 0.10,
];

$lengthArgument = $argv[1] ?? '6';
$samples = isset($argv[2])
    ? (int) $argv[2]
    : DEFAULT_SAMPLES;
$seed = isset($argv[3])
    ? (int) $argv[3]
    : DEFAULT_SEED;

if ($samples < 1) {
    fwrite(STDERR, "Sample count must be positive.\n");

    exit(1);
}

if ($lengthArgument === 'all') {
    $lengths = range(4, 10);
} else {
    $length = (int) $lengthArgument;

    if ($length < 4 || $length > 10) {
        fwrite(STDERR, "Length must be between 4 and 10, or 'all'.\n");

        exit(1);
    }

    $lengths = [$length];
}

$analyzer = new ReadabilityAnalyzer();

$descriptionCostCalculator = new MinimumDescriptionCostCalculator(
    new StructureCostCalculator(),
);

$scorer = new ReadabilityScorer(
    analyzer: $analyzer,
    descriptionCostCalculator: $descriptionCostCalculator,
    transcriptionRiskCalculator: new TranscriptionRiskCalculator(),
);

$thresholdsByLength = [];

foreach ($lengths as $length) {
    $exact = $length <= EXACT_MAX_LENGTH;

    $population = 10 ** $length;
    $iterations = $exact
        ? $population
        : $samples;

    $randomizer = new Randomizer(
        new Mt19937($seed + $length),
    );

    /**
     * @var array<string, array{
     *     score: float,
     *     count: int,
     *     examples: list<string>
     * }> $histogram
     */
    $histogram = [];

    for ($index = 0; $index < $iterations; ++$index) {
        $number = $exact
            ? $index
            : $randomizer->getInt(0, $population - 1);

        $value = str_pad(
            (string) $number,
            $length,
            '0',
            STR_PAD_LEFT,
        );

        $score = $scorer->score(
            new OtpCode($value),
        )->value;

        $key = sprintf('%.12f', $score);

        if (!isset($histogram[$key])) {
            $histogram[$key] = [
                'score' => $score,
                'count' => 0,
                'examples' => [],
            ];
        }

        ++$histogram[$key]['count'];

        if (count($histogram[$key]['examples']) < EXAMPLES_PER_SCORE) {
            $histogram[$key]['examples'][] = $value;
        }
    }

    uasort(
        $histogram,
        static fn (array $left, array $right): int => $right['score'] <=> $left['score'],
    );

    printf(
        "\n%d-digit calibration (%s, %s codes)\n\n",
        $length,
        $exact ? 'exact' : 'sampled',
        number_format($iterations),
    );

    printf(
        "%-12s %10s %12s %12s %14s %s\n",
        'PRESET',
        'BUDGET',
        'THRESHOLD',
        'RETAINED',
        'ENTROPY LOSS',
        'EXAMPLE',
    );

    printf("%s\n", str_repeat('-', 86));

    $lengthThresholds = [];

    foreach ($presetBudgets as $preset => $budget) {
        $targetCount = (int) floor(
            $iterations * $budget,
        );

        $acceptedCount = 0;
        $threshold = null;
        $example = null;

        foreach ($histogram as $bucket) {
            $nextCount = $acceptedCount + $bucket['count'];

            if ($nextCount > $targetCount) {
                break;
            }

            $acceptedCount = $nextCount;
            $threshold = $bucket['score'];
            $example = $bucket['examples'][0] ?? null;
        }

        if ($threshold === null) {
            throw new RuntimeException(
                sprintf(
                    'Unable to determine threshold for %s at length %d.',
                    $preset,
                    $length,
                ),
            );
        }

        $retained = $acceptedCount / $iterations;
        $entropyLoss = -log($retained, 2);

        $lengthThresholds[$preset] = $threshold;

        printf(
            "%-12s %9.2f%% %12.6f %11.4f%% %11.4f bits   %s\n",
            $preset,
            $budget * 100,
            $threshold,
            $retained * 100,
            $entropyLoss,
            $example ?? '-',
        );
    }

    $thresholdsByLength[$length] = $lengthThresholds;

    printf("\nHighest-scoring buckets:\n\n");

    $shown = 0;

    foreach ($histogram as $bucket) {
        printf(
            "%.6f  %-8s  %s\n",
            $bucket['score'],
            number_format($bucket['count']),
            implode(', ', $bucket['examples']),
        );

        ++$shown;

        if ($shown >= 15) {
            break;
        }
    }
}

printf("\nPHP threshold table:\n\n");

echo "[\n";

foreach ($thresholdsByLength as $length => $thresholds) {
    printf("    %d => [\n", $length);

    foreach ($thresholds as $preset => $threshold) {
        printf(
            "        '%s' => %.12f,\n",
            $preset,
            $threshold,
        );
    }

    echo "    ],\n";
}

echo "];\n";
