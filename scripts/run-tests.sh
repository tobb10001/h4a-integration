#!/usr/bin/env sh

ROOT=$(dirname $0)/..

echo -n "Running tests... "

TEST_OUTPUT=$($ROOT/vendor/bin/phpunit tests)

if [ $? -ne 0 ]; then
  # Show full output
  echo # missing newline
  echo "$TEST_OUTPUT"

  echo "Tests failed... Exiting with error."
  exit 1
fi

# Show summary (last line)
echo "$TEST_OUTPUT" | tail -n 1
