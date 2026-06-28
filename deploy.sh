#!/usr/bin/env bash
set -euo pipefail

# Change to project root directory
cd "$(dirname "$0")"

# Remove old build archive if exists
rm -f build.zip

# Create a zip archive of the project, excluding the data directory and its contents
zip -r build.zip . -x ".git/*" "build.zip" "data/*" "data/*/*" "data/*/*/*" "data/*/*/*/*"

echo "build.zip created successfully, excluding data/ directory."