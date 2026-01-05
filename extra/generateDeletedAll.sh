#!/bin/bash

set -e

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

if [[ "$#" -ne 3 ]]; then
    echo "$0 <repository> <fromBranch> <toBranch>"
    exit 1
fi

REPOSITORY=$(echo "$1" |sed 's#[^/]$#&/#')
FROM_BRANCH="$2"
TO_BRANCH="$3"

if [[ ! -d "${REPOSITORY}.git/" ]]; then
    echo "The directory ${REPOSITORY} is not a git repository."
    exit 1
fi

bash generateDeletedFilesXml.sh $1 $2 $3
bash generateDeletedPhrases.sh $1 $2 $3
