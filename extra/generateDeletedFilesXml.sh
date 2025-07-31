#!/bin/bash

set -e

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

pushd "${REPOSITORY}" > /dev/null

if [[ -d "./com.woltlab.wcf/" ]]; then
    DIRECTORIES=$'com.woltlab.wcf/templates/\nwcfsetup/install/files/'
else
    DIRECTORIES=$(
        {
            find . -type d -depth 1 -name "acptemplates*";
            find . -type d -depth 1 -name "files*";
            find . -type d -depth 1 -name "templates*";
        }
    )
fi

DELETED_FILES=""
while IFS='' read -r DIRECTORY; do
    if [[ ! -z "${DELETED_FILES}" ]]; then
        DELETED_FILES+=$'\n'
    fi

    DELETED_FILES+=$(git diff -l0 --diff-filter=D --name-only "${FROM_BRANCH}...${TO_BRANCH}" -- "${DIRECTORY}")
done <<< "${DIRECTORIES}"

popd > /dev/null

php _generateDeletedFilesXml.php "${REPOSITORY}" <<< "${DELETED_FILES}"

echo "Done"