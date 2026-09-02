<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\Scoring\ChunkSequence;
use JacyImp\MemorableOtp\Scoring\GroupedSequence;
use JacyImp\MemorableOtp\Scoring\MinimumDescriptionCostCalculator;
use JacyImp\MemorableOtp\Scoring\Mirror;
use JacyImp\MemorableOtp\Scoring\PeriodicPattern;
use JacyImp\MemorableOtp\Scoring\ReadabilityAnalyzer;
use JacyImp\MemorableOtp\Scoring\ReadabilityScorer;
use JacyImp\MemorableOtp\Scoring\RepeatedChunk;
use JacyImp\MemorableOtp\Scoring\RoundNumber;
use JacyImp\MemorableOtp\Scoring\Run;
use JacyImp\MemorableOtp\Scoring\Sequence;
use JacyImp\MemorableOtp\Scoring\StructureCostCalculator;
use JacyImp\MemorableOtp\Scoring\TranscriptionRiskCalculator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MutationRegressionTest extends TestCase
{
    #[Test]
    public function scoringBehaviorRemainsExactAcrossARepresentativeCorpus(): void
    {
        $analyzer = new ReadabilityAnalyzer();
        $structureCostCalculator = new StructureCostCalculator();
        $costCalculator = new MinimumDescriptionCostCalculator($structureCostCalculator);
        $scorer = new ReadabilityScorer(
            analyzer: $analyzer,
            descriptionCostCalculator: $costCalculator,
            transcriptionRiskCalculator: new TranscriptionRiskCalculator(),
        );
        $context = hash_init('sha256');

        foreach ($this->codes() as $value) {
            $code = new OtpCode($value);
            $analysis = $analyzer->analyze($code);

            hash_update($context, serialize([
                $analysis,
                $costCalculator->describe($analysis),
                $scorer->score($code),
            ]));
        }

        hash_update($context, serialize($this->structureCosts($structureCostCalculator)));

        self::assertSame(
            'cfd8b4b1f751c8a7e3174269a20c8f0aa625a8de4c52c5ae1aeca99d368e6d73',
            hash_final($context),
        );
    }

    /** @return iterable<string> */
    private function codes(): iterable
    {
        for ($value = 0; $value <= 999; ++$value) {
            yield str_pad((string) $value, 4, '0', STR_PAD_LEFT);
        }

        $examples = [
            '00000', '11111', '12121', '12345', '54321', '10000',
            '000000', '010203', '101010', '112233', '123321', '123456', '654321',
            '0123456', '1010101', '1122334', '1234321', '7654321',
            '00000000', '10101010', '11112222', '12344321', '12345678', '87654321',
            '010203040', '123454321', '987654321',
            '0000000000', '0102030405', '1234554321', '9876543210',
            '0123456789', '1111121212', '1212121212', '1000100010',
            '1011121314', '1020304050', '9080706050', '0504030201',
            '1112223334', '9998887776', '1122334455', '0010010012',
            '7121212121', '1212121217', '7012012012', '1231231234',
            '8123123123', '1231231238', '9000012345', '1234500000',
        ];

        foreach ($examples as $value) {
            yield $value;
        }

        foreach (['12', '13', '19', '05', '123', '120', '121', '001'] as $unit) {
            for ($repetitions = 2; $repetitions <= 5; ++$repetitions) {
                $pattern = substr(str_repeat($unit, $repetitions), 0, 10);

                if (strlen($pattern) < 4) {
                    continue;
                }

                yield $pattern;
                yield substr('7' . $pattern, 0, 10);
                yield substr($pattern . '8', 0, 10);
            }
        }

        for ($length = 5; $length <= 10; ++$length) {
            for ($seed = 0; $seed < 500; ++$seed) {
                $digest = hash('sha256', $length . ':' . $seed);
                $value = '';

                for ($index = 0; $index < $length; ++$index) {
                    $value .= (string) (hexdec($digest[$index]) % 10);
                }

                yield $value;
            }
        }
    }

    /** @return list<float> */
    private function structureCosts(StructureCostCalculator $calculator): array
    {
        $costs = [];

        for ($length = 2; $length <= 10; ++$length) {
            $costs[] = $calculator->forRun(new Run('1', $length, 0));
            $costs[] = $calculator->forMirror(new Mirror(str_repeat('1', $length), 0));
            $costs[] = $calculator->forPeriodicPattern(new PeriodicPattern('12', $length, 0));
        }

        for ($repetitions = 2; $repetitions <= 5; ++$repetitions) {
            $costs[] = $calculator->forRepeatedChunk(new RepeatedChunk('12', $repetitions, 0));
        }

        foreach ([-9, -5, -3, -2, -1, 1, 2, 3, 5, 9] as $step) {
            $costs[] = $calculator->forSequence(new Sequence(5, $step, 6, 0));
            $costs[] = $calculator->forGroupedSequence(new GroupedSequence(5, $step, 2, 4, 0));
        }

        foreach ([1, 2, 3, 5, 9, 10, 20, 30, 50, 100] as $step) {
            $costs[] = $calculator->forChunkSequence(new ChunkSequence(10, $step, 2, 4, 0));
        }

        for ($zeroes = 1; $zeroes <= 5; ++$zeroes) {
            $value = '1' . str_repeat('0', $zeroes);
            $costs[] = $calculator->forRoundNumber(new RoundNumber($value, 0, $zeroes));
        }

        return $costs;
    }
}
