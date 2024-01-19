#!/bin/bash

# Define the license header
license_header=$(cat <<'EOF'
<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

EOF
)

# Define the file path
file_path=$1

# Get the line number of the namespace declaration
line_number=$(grep -n -m 1 '^namespace' $file_path | cut -d: -f1)

# Replace the content before the namespace declaration with the license header
cat $file_path | awk -v n=$line_number -v header="$license_header" 'NR<n{next} NR==n{print header; print ""} 1' > $file_path.tmp && mv $file_path.tmp $file_path

