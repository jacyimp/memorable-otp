<?php

declare(strict_types=1);

namespace JacyImp\MemorableOtp\Scoring;

use JacyImp\MemorableOtp\OtpLength;
use JacyImp\MemorableOtp\ReadabilityPreset;

final readonly class PresetCalibrationProvider
{
    private const CALIBRATIONS = [
        4 => [
            'readable' => [
                'threshold' => 0.141015625000,
                'retained' => 0.478300,
                'exact' => true,
            ],
            'easy' => [
                'threshold' => 0.187500000000,
                'retained' => 0.219300,
                'exact' => true,
            ],
            'veryEasy' => [
                'threshold' => 0.197809214877,
                'retained' => 0.172600,
                'exact' => true,
            ],
            'superEasy' => [
                'threshold' => 0.265417771548,
                'retained' => 0.137500,
                'exact' => true,
            ],
            'uberEasy' => [
                'threshold' => 0.336562500000,
                'retained' => 0.096500,
                'exact' => true,
            ],
        ],
        5 => [
            'readable' => [
                'threshold' => 0.129864660929,
                'retained' => 0.442270,
                'exact' => true,
            ],
            'easy' => [
                'threshold' => 0.189940192185,
                'retained' => 0.286550,
                'exact' => true,
            ],
            'veryEasy' => [
                'threshold' => 0.245871136269,
                'retained' => 0.199340,
                'exact' => true,
            ],
            'superEasy' => [
                'threshold' => 0.288244078357,
                'retained' => 0.145660,
                'exact' => true,
            ],
            'uberEasy' => [
                'threshold' => 0.311092605692,
                'retained' => 0.098640,
                'exact' => true,
            ],
        ],
        6 => [
            'readable' => [
                'threshold' => 0.120992584731,
                'retained' => 0.492394,
                'exact' => true,
            ],
            'easy' => [
                'threshold' => 0.197428012145,
                'retained' => 0.299984,
                'exact' => true,
            ],
            'veryEasy' => [
                'threshold' => 0.235645725853,
                'retained' => 0.199787,
                'exact' => true,
            ],
            'superEasy' => [
                'threshold' => 0.273863439560,
                'retained' => 0.140665,
                'exact' => true,
            ],
            'uberEasy' => [
                'threshold' => 0.311841510709,
                'retained' => 0.094143,
                'exact' => true,
            ],
        ],
        7 => [
            'readable' => [
                'threshold' => 0.140011436258,
                'retained' => 0.498568,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.195707028832,
                'retained' => 0.298706,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.234391169511,
                'retained' => 0.198652,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.262553634042,
                'retained' => 0.147060,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.292422824367,
                'retained' => 0.097874,
                'exact' => false,
            ],
        ],
        8 => [
            'readable' => [
                'threshold' => 0.146038810157,
                'retained' => 0.494027,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.199340461925,
                'retained' => 0.295541,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.236194258079,
                'retained' => 0.199916,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.256901041667,
                'retained' => 0.147467,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.285449218750,
                'retained' => 0.099770,
                'exact' => false,
            ],
        ],
        9 => [
            'readable' => [
                'threshold' => 0.148971193416,
                'retained' => 0.499363,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.203551141225,
                'retained' => 0.298887,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.232875344511,
                'retained' => 0.198879,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.254206939385,
                'retained' => 0.149751,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.282926492340,
                'retained' => 0.099961,
                'exact' => false,
            ],
        ],
        10 => [
            'readable' => [
                'threshold' => 0.155085741834,
                'retained' => 0.498952,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.203609890788,
                'retained' => 0.299586,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.234153476428,
                'retained' => 0.199909,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.254951286765,
                'retained' => 0.149989,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.280147497402,
                'retained' => 0.099992,
                'exact' => false,
            ],
        ],
    ];

    public function calibration(
        OtpLength $length,
        ReadabilityPreset $preset,
    ): PresetCalibration {
        $calibration = self::CALIBRATIONS[$length->value][$preset->value];

        return new PresetCalibration(
            threshold: $calibration['threshold'],
            retainedFraction: $calibration['retained'],
            exact: $calibration['exact'],
        );
    }
}
