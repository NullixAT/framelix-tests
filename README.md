![UnitTestsBadge](https://github.com/NullixAT/framelix-tests/actions/workflows/unit-tests.yml/badge.svg) ![CoverageBadge](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/brainfoolong/2e4ba189fbb1a23bff14e73cb893bc3e/raw/framelix-unit-tests-coverage-data.json)

# Framelix Core Tests

This repository contains the unit tests for the Framelix Core.

## Requirements

PHP 8.1+, a mysql database, NodeJs and composer is required to run the unit tests.

## Setup

The Unit Tests requires 2 repositories. This, and the core itself.

    git clone https://github.com/NullixAT/framelix-tests.git
    cd framelix-tests
    git clone https://github.com/NullixAT/framelix-core.git modules/Framelix
    composer install
    cd modules/Framelix
    npm install

Open the app in your browser and finish the setp.