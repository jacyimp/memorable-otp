# Memorable OTP

[![Coverage: 100%](https://img.shields.io/badge/coverage-100%25-brightgreen)](https://github.com/jacyimp/memorable-otp/actions/workflows/ci.yml)
[![MSI: 100%](https://img.shields.io/badge/MSI-100%25-brightgreen)](https://github.com/jacyimp/memorable-otp/actions/workflows/ci.yml)
[![PHPStan: max](https://img.shields.io/badge/PHPStan-max-brightgreen)](https://github.com/jacyimp/memorable-otp/actions/workflows/ci.yml)

Human-friendly numeric verification codes for PHP, while keeping as many possible codes as practical.

## Install

```bash
composer require jacyimp/memorable-otp
```

## Usage

```php
use JacyImp\MemorableOtp\MemorableOtp;

$code = MemorableOtp::readable(6); // e.g. 159915
```

Supported lengths: `4–10`.

```php
MemorableOtp::readable(4); // e.g. 0112
MemorableOtp::readable(6); // e.g. 159915
MemorableOtp::readable(8); // e.g. 49649440
```

## Readability levels

```php
MemorableOtp::readable(6);  // e.g. 159915
MemorableOtp::easy(6);      // e.g. 100212
MemorableOtp::veryEasy(6);  // e.g. 012013
MemorableOtp::superEasy(6); // e.g. 010203
MemorableOtp::uberEasy(6);  // e.g. 121212
```

| Preset        | Possible codes kept | Entropy loss* |
| ------------- | ------------------: | ------------: |
| `readable()`  |           up to 50% |     ≥ 1.0 bit |
| `easy()`      |           up to 30% |    ≥ 1.7 bits |
| `veryEasy()`  |           up to 20% |    ≥ 2.3 bits |
| `superEasy()` |           up to 15% |    ≥ 2.7 bits |
| `uberEasy()`  |           up to 10% |    ≥ 3.3 bits |

* Actual values vary slightly by code length.

## What it recognizes

```text
121212   repeated chunk
112233   grouped sequence
123456   sequence
654321   descending sequence
123321   mirror
010203   chunk sequence
81818    periodic pattern
10020    round-number structure
```

Long identical runs are penalized to reduce transcription mistakes:

```text
111111
```

## Explicit preset

```php
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;

$code = MemorableOtp::generate(
    length: 7,
    preset: ReadabilityPreset::VeryEasy,
); // e.g. 4015642
```

Equivalent to:

```php
$code = MemorableOtp::veryEasy(7); // e.g. 4015642
```

## Security

Candidates are generated with cryptographically secure randomness and filtered using rejection sampling.

The first candidate that meets the selected readability threshold is returned. Accepted codes are not ranked against each other.

```php
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;

$security = MemorableOtp::security(
    length: 7,
    preset: ReadabilityPreset::UberEasy,
);

$security->rawSearchSpace();
$security->acceptedSearchSpace();
$security->entropyBits();
$security->entropyLossBits();
$security->exact;
```

Normal OTP protections still apply: expiration, attempt limits, rate limiting, one-time use, secure storage, and secure delivery.

## Requirements

PHP 8.2+

## License

MIT
