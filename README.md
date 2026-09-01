# Memorable OTP

Human-friendly numeric verification codes for PHP.

```bash
composer require jacyimp/memorable-otp
```

## Usage

```php
use JacyImp\MemorableOtp\MemorableOtp;

$code = MemorableOtp::readable();
```

Choose how aggressively readability should be favored:

```php
MemorableOtp::readable(6);
MemorableOtp::easy(6);
MemorableOtp::veryEasy(6);
MemorableOtp::superEasy(6);
MemorableOtp::uberEasy(7);
```

Examples of memorable codes include:

```text
112233
121212
123456
010203
10020
81818
```

## Presets

Readability comes at the cost of reducing the accepted search space.

| Preset        | Target retained space | Approx. entropy loss |
| ------------- | --------------------: | -------------------: |
| `readable()`  |                   50% |             1.00 bit |
| `easy()`      |                   30% |            1.74 bits |
| `veryEasy()`  |                   20% |            2.32 bits |
| `superEasy()` |                   15% |            2.74 bits |
| `uberEasy()`  |                   10% |            3.32 bits |

Thresholds are calibrated independently for each supported OTP length.

For example, a 7-digit `uberEasy()` code retains roughly one million possible values, approximately the same search-space size as an unrestricted 6-digit code.

## Explicit preset

```php
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;

$code = MemorableOtp::generate(
    length: 7,
    preset: ReadabilityPreset::VeryEasy,
);
```

## Security estimate

The effect of a preset on the search space can be inspected directly:

```php
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

Calibrations for 4–6 digit codes are exhaustive. Calibrations for 7–10 digit codes are based on large deterministic samples and are therefore approximate.

## How it works

Candidates are generated using cryptographically secure randomness and scored for structures that make numeric codes easier to read and remember briefly.

Memorable OTP uses rejection sampling:

1. Generate a uniformly random numeric code.
2. Score its readability.
3. Reject it if it does not meet the selected preset.
4. Return the first code that does.

It does **not** generate several candidates and return the best one.

As a result, every code accepted by a preset remains equally likely to be generated.

## Security

Memorable OTP deliberately trades some search space for readability. The stricter the preset, the larger that tradeoff.

It does not replace normal verification-code security measures such as short expiration times, attempt limits, rate limiting, secure storage, and one-time use.

If preserving approximately the search space of a standard 6-digit code matters, prefer increasing the length when using stricter presets. For example:

```php
MemorableOtp::uberEasy(7);
```

## Requirements

PHP 8.2+

## License

MIT
