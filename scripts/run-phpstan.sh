#!/usr/bin/env sh

ROOT=$(dirname $0)/..

echo -n "Running PHPStan... "

PHPSTAN_OUTPUT=$($ROOT/vendor/bin/phpstan --no-progress --configuration=$ROOT/phpstan.neon)

if [ $? -ne 0 ]; then
    # Show full output
    echo # missing newline
    echo "$PHPSTAN_OUTPUT"

    echo "PHPStan-Analysis failed... Exiting with error."
    exit 1;
fi

echo "no errors."
