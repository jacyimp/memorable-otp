<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Tests\Generation;

use JacyImp\MemorableOtp\Generation\CandidateGenerator;
use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\OtpLength;
use RuntimeException;

final class SequenceCandidateGenerator implements CandidateGenerator
{
    /**
     * @var list<string>
     */
    private array $candidates;

    /**
     * @param list<string> $candidates
     */
    public function __construct(array $candidates)
    {
        $this->candidates = $candidates;
    }

    public function generate(OtpLength $length): OtpCode
    {
        $candidate = array_shift($this->candidates);

        if ($candidate === null) {
            throw new RuntimeException(
                'No deterministic candidates remaining.',
            );
        }

        return new OtpCode($candidate);
    }
}
