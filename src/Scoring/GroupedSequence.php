<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

final readonly class GroupedSequence
{
    public function __construct(
        public int $start,
        public int $step,
        public int $groupLength,
        public int $groups,
        public int $offset,
    ) {
    }

    public function length(): int
    {
        return $this->groupLength * $this->groups;
    }
}
