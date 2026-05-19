#!/usr/bin/env bash
# Download chamber logos from the web and attach field_logo (Drush mcil).
#
# Usage:
#   ./scripts/chamber-import-logos.sh
#   ./scripts/chamber-import-logos.sh --force
#   ./scripts/chamber-import-logos.sh --title="Saudi Chambers"
set -euo pipefail
cd "$(dirname "$0")/.."
ddev drush mcil "$@"
