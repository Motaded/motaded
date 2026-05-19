#!/usr/bin/env bash
# Download platform logos from the web and attach field_logo (Drush mpil).
#
# Usage:
#   ./scripts/platform-import-logos.sh
#   ./scripts/platform-import-logos.sh --force
#   ./scripts/platform-import-logos.sh --title="ZATCA"
set -euo pipefail
cd "$(dirname "$0")/.."
ddev drush mpil "$@"
