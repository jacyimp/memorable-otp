# Memorable OTP

Human-friendly numeric verification codes for PHP, while keeping the pool of possible codes as large as practical.

Instead of:

```text
583917
472861
936504
```

generate codes more like:

```text
121212
112233
123456
123321
010203
81818
10020
```

Candidates are generated with cryptographically secure randomness and filtered for readability.

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

Higher levels filter more aggressively for readable structure.

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

Digit diversity and transcription risk are also considered.

For example:

```text
111111
```

is simple, but penalized because long identical runs are easier to miscount.

## Explicit preset

```php
use JacyImp\MemorableOtp\MemorableOtp;
use JacyImp\MemorableOtp\ReadabilityPreset;

$code = MemorableOtp::generate(
    length: 7,
    preset: ReadabilityPreset::VeryEasy,
);
```

Equivalent to:

```php
$code = MemorableOtp::veryEasy(7);
```

## Security

Readability comes from rejecting some otherwise valid random codes.

Memorable OTP uses rejection sampling:

```text
secure random candidate
          ↓
   readability score
          ↓
   meets threshold?
      ↙       ↘
    no         yes
    ↓           ↓
  retry       return
```

The first candidate that passes is returned.

Accepted candidates are not ranked against each other, so every code accepted by a preset remains equally likely to be returned.

Security estimates can be inspected directly:

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

For stricter presets, increasing the length helps compensate for the reduced pool of accepted codes.

```php
MemorableOtp::uberEasy(6); // e.g. 121212
MemorableOtp::uberEasy(7); // e.g. 1212121
```

Normal OTP protections still apply: expiration, attempt limits, rate limiting, one-time use, secure storage, and secure delivery.

## Requirements

PHP 8.2+

## License

MIT
