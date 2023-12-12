#!/bin/bash

set -e

schema="packages/guides-cli/resources/schema/guides.xsd"
directory="."

for file in $(find "$directory" -type f -name "guides.xml" -not \( -path "./.git*" -o -path "./.cache*" \)); do
    xmllint --noout --schema "$schema" "$file"
done
