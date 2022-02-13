#!/usr/bin/env bash

STASH_NAME="pre-commit-$(date +%s)"

git stash push -q --keep-index -m $STASH_NAME

./scripts/run-tests.sh
RESULT=$?

STASHES=$(git stash list | cut -d " " -f 4)
if [[ $STASHES == "$STASH_NAME" ]]; then
    git stash pop -q
fi

if [ $RESULT -ne 0 ]; then
    exit 1
fi

exit 0
