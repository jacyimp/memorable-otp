<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Generation;

use JacyImp\MemorableOtp\OtpCode;
use JacyImp\MemorableOtp\OtpLength;

interface CandidateGenerator
{
    public function generate(OtpLength $length): OtpCode;
}
