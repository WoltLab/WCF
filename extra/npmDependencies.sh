#!/bin/bash
set -e

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd "${SCRIPT_DIR}/../"
grep ' node_modules/' .github/workflows/javascript.yml |awk '{print $4 " " $3}' |xargs -n 2 cp
