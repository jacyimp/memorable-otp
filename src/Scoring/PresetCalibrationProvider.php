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
                'threshold' => 0.125781250000,
                'retained' => 0.473200,
                'exact' => true,
            ],
            'easy' => [
                'threshold' => 0.184062500000,
                'retained' => 0.286300,
                'exact' => true,
            ],
            'veryEasy' => [
                'threshold' => 0.187500000000,
                'retained' => 0.163600,
                'exact' => true,
            ],
            'superEasy' => [
                'threshold' => 0.250000000000,
                'retained' => 0.136800,
                'exact' => true,
            ],
            'uberEasy' => [
                'threshold' => 0.312812500000,
                'retained' => 0.081200,
                'exact' => true,
            ],
        ],
        5 => [
            'readable' => [
                'threshold' => 0.100000000000,
                'retained' => 0.460860,
                'exact' => true,
            ],
            'easy' => [
                'threshold' => 0.165169317134,
                'retained' => 0.296420,
                'exact' => true,
            ],
            'veryEasy' => [
                'threshold' => 0.200957650351,
                'retained' => 0.197970,
                'exact' => true,
            ],
            'superEasy' => [
                'threshold' => 0.251915300702,
                'retained' => 0.148110,
                'exact' => true,
            ],
            'uberEasy' => [
                'threshold' => 0.326488156714,
                'retained' => 0.087680,
                'exact' => true,
            ],
        ],
        6 => [
            'readable' => [
                'threshold' => 0.115248689930,
                'retained' => 0.460452,
                'exact' => true,
            ],
            'easy' => [
                'threshold' => 0.179079403124,
                'retained' => 0.299631,
                'exact' => true,
            ],
            'veryEasy' => [
                'threshold' => 0.227892670715,
                'retained' => 0.197634,
                'exact' => true,
            ],
            'superEasy' => [
                'threshold' => 0.263459858364,
                'retained' => 0.149513,
                'exact' => true,
            ],
            'uberEasy' => [
                'threshold' => 0.281647917706,
                'retained' => 0.099234,
                'exact' => true,
            ],
        ],
        7 => [
            'readable' => [
                'threshold' => 0.120674373112,
                'retained' => 0.489388,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.184409417893,
                'retained' => 0.297092,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.227878747678,
                'retained' => 0.196702,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.247147154979,
                'retained' => 0.149860,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.272317369142,
                'retained' => 0.099500,
                'exact' => false,
            ],
        ],
        8 => [
            'readable' => [
                'threshold' => 0.129882812500,
                'retained' => 0.484424,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.186944321346,
                'retained' => 0.299780,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.223437500000,
                'retained' => 0.198230,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.242343750000,
                'retained' => 0.149952,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.277321783712,
                'retained' => 0.099774,
                'exact' => false,
            ],
        ],
        9 => [
            'readable' => [
                'threshold' => 0.134123186597,
                'retained' => 0.496262,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.189211386780,
                'retained' => 0.298216,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.221687845718,
                'retained' => 0.199066,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.243532561272,
                'retained' => 0.148772,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.271030991786,
                'retained' => 0.099608,
                'exact' => false,
            ],
        ],
        10 => [
            'readable' => [
                'threshold' => 0.140285069480,
                'retained' => 0.497066,
                'exact' => false,
            ],
            'easy' => [
                'threshold' => 0.189968143886,
                'retained' => 0.299960,
                'exact' => false,
            ],
            'veryEasy' => [
                'threshold' => 0.220513266184,
                'retained' => 0.197752,
                'exact' => false,
            ],
            'superEasy' => [
                'threshold' => 0.239368966959,
                'retained' => 0.148284,
                'exact' => false,
            ],
            'uberEasy' => [
                'threshold' => 0.267045400634,
                'retained' => 0.099452,
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
