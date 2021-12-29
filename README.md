# Framelix Core Unit Tests

This repository contains the unit tests for the Framelix Core.

## Pre-Alpha

This repository is currently in pre-alpha, in constant changes. I develop the core and tests from ground up. I let you
know when there is something new to test it out. I will try to make some projects based on this framework available in
early/mid 2022.

## Setup

Framelix uses git submodules. So after cloning you have to initialize the submodules.

    git clone https://github.com/NullixAT/framelix-unit-tests.git
    cd framelix-unit-tests
    git submodule update --init --recursive

Copy `modules/FramelixUnitTests/config/config-editable-template.php`
to `modules/FramelixUnitTests/config/config-editable.php` and edit contents to your needs.

## Development

To develop in this library there are 2 modules.

* Framelix - Which is the core itself and integrated as a submodule
* FramelixUnitTests - The actual test module where all tests live in
*

#### Submodule development

Development on a submodule is not the same as default git development. A submodule is a "independent" part in this
repository which need to be handled separetely.

More about submodules here: https://git-scm.com/book/en/v2/Git-Tools-Submodules

After cloning and you plan to work in the submodule as well, you have to checkout a submodule branch, eg: `main`. Update
sources before checkout, if your clone is a little older.

Go into `Framelix` submodule folder and run `git checkout main`.

Now you can work and commit in the submodule as well is outside of it.

Depending on where you have made changes, you may need to:

1. Commit and push in this repository with default git methods
2. Commit and push in `modules/Framework` with default git methods
    1. After you have updated the submodule, you may need to update the submodule revision as well by doing step 1 again