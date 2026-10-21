#!/bin/bash
set -Eeuo pipefail

# Double-clickable macOS entry point for the local-only Content Studio.
# Both servers bind to 127.0.0.1. Nothing is installed and nothing deploys.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
HOST="127.0.0.1"
STUDIO_PORT="${CONTENT_STUDIO_PORT:-4173}"
PREVIEW_PORT="${CONTENT_PREVIEW_PORT:-8088}"
REQUESTED_STUDIO_PORT="$STUDIO_PORT"
LOG_DIR="${HOME}/Library/Logs/GarhJaisalContentStudio"
STUDIO_LOG="${LOG_DIR}/content-studio.log"
PREVIEW_LOG="${LOG_DIR}/php-preview.log"
STUDIO_PID=""
PREVIEW_PID=""

fail() {
  printf '\nERROR: %s\n' "$1" >&2
  exit 1
}

if [[ ! -f "$ROOT/scripts/content-studio.js" || ! -f "$ROOT/tools/content-studio/index.html" ]]; then
  fail "The project folder is incomplete. Run this file from inside the garh-jaisal-website checkout."
fi

NODE_BIN="$(command -v node || true)"
[[ -n "$NODE_BIN" ]] || fail "Node.js is not available in PATH. Ask your operator to install the project's supported Node.js version, then reopen this launcher."

if [[ -n "${PHP_BIN:-}" ]]; then
  PHP_BIN="$(command -v "$PHP_BIN" || true)"
elif command -v php >/dev/null 2>&1; then
  PHP_BIN="$(command -v php)"
elif [[ -x "/Applications/XAMPP/xamppfiles/bin/php" ]]; then
  PHP_BIN="/Applications/XAMPP/xamppfiles/bin/php"
else
  PHP_BIN=""
fi
[[ -n "$PHP_BIN" && -x "$PHP_BIN" ]] || fail "PHP was not found. Put PHP on PATH or install/repair XAMPP, then reopen this launcher."

command -v curl >/dev/null 2>&1 || fail "curl is missing; this macOS launcher uses it only to check local server readiness."
command -v lsof >/dev/null 2>&1 || fail "lsof is missing; this launcher uses it to avoid stopping or replacing another local service."

validate_port() {
  local port="$1"
  [[ "$port" =~ ^[0-9]{1,5}$ ]] || fail "Invalid local port: $port"
  (( port > 0 && port < 65536 )) || fail "Invalid local port: $port"
}

choose_available_port() {
  local label="$1"
  local candidate="$2"
  local reserved="${3:-}"
  local attempts=0

  validate_port "$candidate"
  while (( attempts < 100 )); do
    if [[ "$candidate" != "$reserved" ]] && ! lsof -nP -iTCP:"$candidate" -sTCP:LISTEN >/dev/null 2>&1; then
      printf '%s\n' "$candidate"
      return 0
    fi
    candidate=$((candidate + 1))
    attempts=$((attempts + 1))
    (( candidate < 65536 )) || break
  done

  fail "Could not find an available $label port within 100 ports starting at $2. Set CONTENT_STUDIO_PORT or CONTENT_PREVIEW_PORT to a free port and retry."
}

validate_port "$PREVIEW_PORT"
if lsof -nP -iTCP:"$PREVIEW_PORT" -sTCP:LISTEN >/dev/null 2>&1; then
  fail "Local PHP preview port $PREVIEW_PORT is already in use. No existing service was changed. Stop or reconfigure that service, or set CONTENT_PREVIEW_PORT to a free port and update the Studio preview URL to match."
fi

STUDIO_PORT="$(choose_available_port "Content Studio" "$STUDIO_PORT" "$PREVIEW_PORT")"
STUDIO_URL="http://${HOST}:${STUDIO_PORT}"
PREVIEW_URL="http://${HOST}:${PREVIEW_PORT}"

if [[ "$STUDIO_PORT" != "$REQUESTED_STUDIO_PORT" ]]; then
  printf 'Port %s is occupied; Content Studio will use %s instead. No existing service was changed.\n' "$REQUESTED_STUDIO_PORT" "$STUDIO_PORT"
fi

mkdir -p "$LOG_DIR"
: > "$STUDIO_LOG"
: > "$PREVIEW_LOG"

cleanup() {
  set +e
  for pid in "$STUDIO_PID" "$PREVIEW_PID"; do
    [[ "$pid" =~ ^[0-9]+$ ]] || continue
    kill -TERM "$pid" 2>/dev/null
  done
  for pid in "$STUDIO_PID" "$PREVIEW_PID"; do
    [[ "$pid" =~ ^[0-9]+$ ]] || continue
    wait "$pid" 2>/dev/null
  done
}
trap cleanup EXIT
trap 'exit 130' INT TERM HUP

cd "$ROOT"
"$PHP_BIN" -S "${HOST}:${PREVIEW_PORT}" -t "$ROOT" >> "$PREVIEW_LOG" 2>&1 &
PREVIEW_PID=$!
CONTENT_STUDIO_PORT="$STUDIO_PORT" "$NODE_BIN" scripts/content-studio.js >> "$STUDIO_LOG" 2>&1 &
STUDIO_PID=$!

wait_for_url() {
  local url="$1"
  local pid="$2"
  local label="$3"
  local log="$4"
  local expected="${5:-}"
  local response
  local attempt

  for attempt in {1..40}; do
    if ! kill -0 "$pid" 2>/dev/null; then
      printf '\nERROR: %s stopped during startup. Recent log output:\n' "$label" >&2
      tail -n 30 "$log" >&2 || true
      return 1
    fi
    if response="$(curl --silent --fail --max-time 1 "$url" 2>/dev/null)"; then
      if [[ -z "$expected" || "$response" == *"$expected"* ]]; then
        return 0
      fi
    fi
    sleep 0.25
  done

  if [[ -n "$expected" ]]; then
    printf '\nERROR: %s did not return the expected service identity (%s). The port may be serving a different application.\n' "$label" "$expected" >&2
  fi
  printf '\nERROR: %s did not become ready. Recent log output:\n' "$label" >&2
  tail -n 30 "$log" >&2 || true
  return 1
}

wait_for_url "${PREVIEW_URL}/blog.php" "$PREVIEW_PID" "Local PHP preview" "$PREVIEW_LOG" || exit 1
wait_for_url "${STUDIO_URL}/api/status" "$STUDIO_PID" "Content Studio" "$STUDIO_LOG" "Local authoring only." || exit 1

printf '\nGarh Jaisal Content Studio is ready.\n'
printf 'Editor:  %s\n' "$STUDIO_URL"
printf 'Preview: %s/blog.php\n' "$PREVIEW_URL"
printf 'Logs:    %s\n' "$LOG_DIR"
printf '\nKeep this Terminal window open while you work. Close it to stop both local services.\n'
printf 'Save new content as Draft first. Nothing is deployed by this tool.\n\n'

if command -v open >/dev/null 2>&1; then
  open "$STUDIO_URL" || printf 'Open this address in your browser: %s\n' "$STUDIO_URL"
else
  printf 'Open this address in your browser: %s\n' "$STUDIO_URL"
fi

while kill -0 "$STUDIO_PID" 2>/dev/null && kill -0 "$PREVIEW_PID" 2>/dev/null; do
  sleep 1
done

printf '\nA local service stopped unexpectedly. Review the logs above or ask your operator for help.\n' >&2
exit 1
