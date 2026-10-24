#!/usr/bin/env bash
set -euo pipefail

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

if ! command -v php >/dev/null 2>&1; then
  echo "PHP CLI is required for PHP syntax checks." >&2
  exit 1
fi

find "$ROOT/wp-content/themes/isg-theme" "$ROOT/wp-content/plugins/isg-site" -type f -name '*.php' -print0 \
  | while IFS= read -r -d '' file; do
      php -l "$file"
    done

if command -v python3 >/dev/null 2>&1; then
  python3 - "$ROOT/wp-content/themes/isg-theme/theme.json" <<'PY'
import json, sys
from pathlib import Path
path = Path(sys.argv[1])
with path.open(encoding='utf-8') as handle:
    json.load(handle)
print(f"JSON OK: {path}")
PY
else
  echo "python3 not found; skipped theme.json validation." >&2
fi

echo "Static checks passed. WordPress runtime behavior still requires a WP test site."
