# Memorable OTP

Human-friendly numeric verification codes for PHP.

```bash
composer require jacyimp/memorable-otp
```

## Usage

Generate a readable 6-digit verification code:

```php
use JacyImp\MemorableOtp\MemorableOtp;

$code = MemorableOtp::readable();

echo $code;
```

Or choose the length:

```php
$code = MemorableOtp::readable(7);
```

## Readability levels

```php
use JacyImp\MemorableOtp\MemorableOtp;

MemorableOtp::readable(6);
MemorableOtp::easy(6);
MemorableOtp::veryEasy(6);
MemorableOtp::superEasy(6);
MemorableOtp::uberEasy(7);
```

Examples of codes the library may favor:

```text
112233
121212
123456
010203
102030
81818
10020
```

The stricter the preset, the smaller the accepted search space.

| Preset        | Target retained space | Approx. entropy loss |
| ------------- | --------------------: | -------------------: |
| `readable()`  |                   50% |             1.00 bit |
| `easy()`      |                   30% |            1.74 bits |
| `veryEasy()`  |                   20% |            2.32 bits |
| `superEasy()` |                   15% |            2.74 bits |
| `uberEasy()`  |                   10% |            3.32 bits |

For example:

```php
$code = MemorableOtp::uberEasy(7);
```

A 7-digit `uberEasy()` code retains roughly one million possible values, approximately the search-space size of an unrestricted 6-digit code.

## Explicit preset

You can also pass the preset directly:

```php
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;

$code = MemorableOtp::generate(
    length: 7,
    preset: ReadabilityPreset::VeryEasy,
);
```

## Security estimates

Memorable OTP can expose the estimated security tradeoff for a length and preset:

```php
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;

$security = MemorableOtp::security(
    length: 7,
    preset: ReadabilityPreset::UberEasy,
);

echo $security->rawSearchSpace();
echo $security->acceptedSearchSpace();
echo $security->entropyBits();
echo $security->entropyLossBits();
```

You can also check whether the calibration is exact:

```php
if ($security->exact) {
    // Exhaustively calibrated.
} else {
    // Based on deterministic sampling.
}
```

Calibrations for 4–6 digit codes are exhaustive.

Calibrations for 7–10 digit codes are based on large deterministic samples.

## Example verification flow

Memorable OTP only generates the code. Storage, expiration, delivery, and verification remain application concerns.

```php
use JacyImp\MemorableOtp\MemorableOtp;

$code = MemorableOtp::easy(6);

// Store a hash rather than the OTP itself.
$hash = password_hash($code, PASSWORD_DEFAULT);

// Deliver $code to the user through your preferred channel.
sendVerificationCode($user, $code);
```

Later:

```php
if (!password_verify($submittedCode, $hash)) {
    throw new InvalidVerificationCode();
}
```

Your application should still enforce expiration and attempt limits.

## Choosing a length

If you want stronger readability without shrinking the effective search space too far, increase the OTP length.

For example:

```php
// More readable, but significantly reduces the 6-digit search space.
$code = MemorableOtp::uberEasy(6);

// Roughly preserves the search-space size of an unrestricted 6-digit OTP.
$code = MemorableOtp::uberEasy(7);
```

You can inspect the actual estimate:

```php
$security = MemorableOtp::security(
    length: 7,
    preset: ReadabilityPreset::UberEasy,
);

printf(
    "%.0f possible accepted codes\n",
    $security->acceptedSearchSpace(),
);
```

## How it works

Memorable OTP generates candidates using cryptographically secure randomness:

```text
random candidate
      ↓
readability score
      ↓
meets preset?
   ↙      ↘
 no       yes
 ↓         ↓
retry    return
```

In simplified PHP:

```php
do {
    $candidate = secureRandomCode();
} while (score($candidate) < $threshold);

return $candidate;
```

This is rejection sampling.

The library does **not** generate several candidates and return the nicest one.

That distinction matters because every code accepted by a preset remains equally likely to be returned.

## What makes a code readable?

The scorer recognizes structures such as:

```text
112233      grouped sequence
121212      repeated chunk
81818       periodic pattern
123456      sequence
987654      descending sequence
010203      chunk sequence
123321      mirror
10020       round-number chunks
```

It also considers digit diversity and transcription risk.

For example, a code such as:

```text
111111
```

is extremely easy to compress mentally, but is penalized because repeated identical digits are easier to miscount while transcribing.

## Supported lengths

```php
MemorableOtp::readable(4);
MemorableOtp::readable(5);
MemorableOtp::readable(6);
MemorableOtp::readable(7);
MemorableOtp::readable(8);
MemorableOtp::readable(9);
MemorableOtp::readable(10);
```

Supported range:

```text
4–10 digits
```

## Security

Memorable OTP deliberately exchanges some search space for easier-to-read codes.

It does not replace normal verification-code security measures:

```text
short expiration
attempt limits
rate limiting
one-time use
secure storage
secure delivery
```

For stricter readability presets, increasing the OTP length is recommended.

## Requirements

```text
PHP 8.2+
```

## License

MIT
