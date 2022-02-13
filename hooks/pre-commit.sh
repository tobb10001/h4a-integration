#!/usr/bin/env bash

# Fail on error.

set -e

# Stash before running tests, s.th. only staged files are tested.

STASH_NAME="pre-commit-$(date +%s)"

git stash push -q --keep-index -m $STASH_NAME

function _unstash() {
    STASHES=$(git stash list | cut -d " " -f 4)
    if [[ $STASHES == "$STASH_NAME" ]]; then
        git stash pop -q
    fi
}

trap _unstash EXIT

# Run Tests

./scripts/run-phpstan.sh
./scripts/run-phpunit.sh
