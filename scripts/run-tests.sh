#!/usr/bin/env sh

DIR=$(dirname $0)

echo "Running tests.. "
TEST_OUTPUT=$($DIR/../vendor/bin/phpunit tests)

if [ $? -ne 0 ]; then
  # Show full output
  echo "$TEST_OUTPUT"

  echo "Tests failed... Exiting with error."
  exit 1
fi

# Show summary (last line)
echo "$TEST_OUTPUT" | tail -n 1
