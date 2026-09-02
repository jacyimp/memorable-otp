<?php

declare(strict_types=1);

$path = $argv[1] ?? null;

if ($path === null || !is_file($path)) {
    fwrite(STDERR, "Usage: php tools/check-coverage.php <clover.xml>\n");

    exit(2);
}

$document = new DOMDocument();

if (!$document->load($path)) {
    fwrite(STDERR, sprintf("Unable to read coverage report: %s\n", $path));

    exit(2);
}

$metrics = (new DOMXPath($document))->query('/coverage/project/metrics')->item(0);

if (!$metrics instanceof DOMElement) {
    fwrite(STDERR, "Coverage report does not contain project metrics.\n");

    exit(2);
}

$measurements = [
    'Methods' => ['coveredmethods', 'methods'],
    'Lines' => ['coveredstatements', 'statements'],
];
$complete = true;

foreach ($measurements as $label => [$coveredAttribute, $totalAttribute]) {
    $covered = (int) $metrics->getAttribute($coveredAttribute);
    $total = (int) $metrics->getAttribute($totalAttribute);
    $percentage = $total === 0
        ? 0.0
        : ($covered / $total) * 100;

    printf(
        "%s: %.2f%% (%d/%d)\n",
        $label,
        $percentage,
        $covered,
        $total,
    );

    if ($total === 0 || $covered !== $total) {
        $complete = false;
    }
}

if ($complete) {
    fwrite(STDOUT, "Coverage requirement satisfied.\n");

    exit(0);
}

fwrite(STDERR, "Coverage must remain at 100%.\n");

exit(1);
