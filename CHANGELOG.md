# Changelog

All notable changes to this project will be documented in this file.

## [0.1.0] - 2026-09-01

### Added

* Human-friendly numeric OTP generation for PHP 8.2+.
* Five readability presets: `readable`, `easy`, `veryEasy`, `superEasy`, and `uberEasy`.
* Cryptographically secure candidate generation using rejection sampling.
* Length-specific readability thresholds calibrated for 4–10 digit codes.
* Readability scoring for repetitions, sequences, grouped sequences, periodic patterns, mirrors, runs, and round numbers.
* Transcription-risk penalties for patterns that are easy to miscount.
* Security estimates exposing retained search space and entropy loss.
* Explainable readability analysis and calibration tools.
