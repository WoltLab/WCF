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

if [[ -d "${REPOSITORY}com.woltlab.wcf/" ]]; then
    DIRECTORY="${REPOSITORY}wcfsetup/install/lang/"
else
    DIRECTORY="${REPOSITORY}language/"
fi

ABSOLUTE_PATH=$(realpath "${DIRECTORY}")

pushd "${DIRECTORY}" > /dev/null

for FILENAME in *.xml; do
    php "${SCRIPT_DIR}/_generateDeletedPhrases.php" "${ABSOLUTE_PATH}/${FILENAME}" <<< $(git show "${FROM_BRANCH}:./${FILENAME}")
done

popd > /dev/null

echo "Done"