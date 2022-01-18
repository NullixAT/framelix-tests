![UnitTestsBadge](https://github.com/NullixAT/framelix-tests/actions/workflows/unit-tests.yml/badge.svg) ![CoverageBadge](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/brainfoolong/2e4ba189fbb1a23bff14e73cb893bc3e/raw/framelix-unit-tests-coverage-data.json)

# Framelix Core Tests

This repository contains the unit tests for the Framelix Core.

## Pre-Alpha

This repository is currently in pre-alpha, in constant changes. I develop the core and tests from ground up. I let you
know when there is something new to test it out. I will try to make some projects based on this framework available in
early/mid 2022.

## Requirements

PHP 8.1+, a mysql database and composer is required to run the unit tests.

## Setup

The Unit Tests requires 2 repositories. This, and the core itself.

    git clone https://github.com/NullixAT/framelix-unit-tests.git
    cd framelix-unit-tests
    git clone https://github.com/NullixAT/framelix-core.git modules/Framelix
    composer install

Copy `modules/FramelixTests/config/config-editable-template.php`
to `modules/FramelixTests/config/config-editable.php` and edit contents to your needs.

## Development

To develop in this library there are 2 modules.

* Framelix - Which is the core itself in a separate repository
* FramelixTests - The actual test module where all tests live in