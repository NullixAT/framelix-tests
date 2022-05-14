![UnitTestsBadge](https://github.com/NullixAT/framelix-tests/actions/workflows/unit-tests.yml/badge.svg) ![CoverageBadge](https://img.shields.io/endpoint?url=https://gist.githubusercontent.com/brainfoolong/2e4ba189fbb1a23bff14e73cb893bc3e/raw/framelix-unit-tests-coverage-data.json)

# Framelix Full-Stack PHP Framework Tests

This repository contains the unit tests for the [Framelix Full-Stack PHP Framework](https://github.com/NullixAT/framelix-core).

## Setup

You need `docker` and `docker-compose` installed.

```
git clone https://github.com/NullixAT/framelix-docker.git framelix-tests
cd framelix-tests
cp config/env-default .env
git clone https://github.com/NullixAT/framelix-tests.git app
git clone https://github.com/NullixAT/framelix-core.git app/modules/Framelix
cp app/modules/FramelixTests/config/config-editable-local.php app/modules/FramelixTests/config/config-editable.php
docker-compose up -d --build
docker-compose exec phpfpm bash -c "cd /framelix && sh composer-setup.sh"
docker-compose exec phpfpm bash -c "cd /framelix && php composer.phar install"
```

## Run PhpStan Static Analyzer

```
sh app/phpstan.sh
```

## Run all PhpUnit tests on command-line

```
sh app/phpunit.sh
```

## Configure PHPStorm IDE to use docker to run tests

* [CLI Interpreter Settings](docs/phpstorm-cli.png)
* [Test Framework Settings](docs/phpstorm-testframeworks.png)
* [Run a single test or multiple tests](docs/phpstorm-run-test.png)
