# Framelix Core Unit Tests

This repository contains the unit tests for the Framelix Core.

## Pre-Alpha

This repository is currently in pre-alpha, in constant changes. I develop the core and tests from ground up. I let you know when there is something new to test it out. I will try to make some projects based on this framework available in early/mid 2022.


## Setup

Framelix uses git submodules. So after cloning you have to initialize the submodules.

    git clone https://github.com/NullixAT/framelix-unit-tests.git
    cd framelix-unit-tests
    git submodule update --init --recursive

## Development

To develop in this library there are 2 modules.
* Framelix - Which is the core itself and integrated as a submodule
* FramelixUnitTests - The actual test module where all tests live in

#### Submodule Tips
If you're clone is a little older, update it to newest source with `git pull && git submodule update --remote`. This will update the submodule as well.