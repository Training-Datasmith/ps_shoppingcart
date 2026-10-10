#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
IMAGE="php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143"
PHAR="$(bash "${ROOT}/tests/verify-phpunit.sh")"
RESULTS_DIR="$(mktemp -d)"
JUNIT="${RESULTS_DIR}/junit.xml"
LOG="${RESULTS_DIR}/phpunit.log"
SHUFFLE_LOG="${RESULTS_DIR}/phpunit-shuffle.log"

DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

run_phpunit() {
  local config="$1"
  local logfile="$2"
  "${DOCKER[@]}" run --rm --entrypoint php \
    -v "${ROOT}:/src" -w /src \
    -v "${PHAR}:/opt/phpunit-4.8.36.phar:ro" \
    "${IMAGE}" \
    -d error_reporting=-1 \
    -d display_errors=stderr \
    -d display_startup_errors=1 \
    -d log_errors=0 \
    -d output_buffering=0 \
    /opt/phpunit-4.8.36.phar \
    --configuration "${config}" \
    --log-junit "/tmp/junit.xml" \
    >"${logfile}" 2>&1 || true
}

assert_clean_log() {
  local logfile="$1"
  if grep -q '^Warning:' "${logfile}"; then
    echo "PHPUnit warnings detected in ${logfile}" >&2
    cat "${logfile}" >&2
    exit 1
  fi
  if grep -Eq '(Notice:|Warning:|Deprecated:|Strict Standards:|Parse error:|Fatal error:)' "${logfile}"; then
    echo "PHP diagnostics detected in ${logfile}" >&2
    cat "${logfile}" >&2
    exit 1
  fi
  if ! grep -q '^OK (' "${logfile}"; then
    echo "Expected OK summary missing in ${logfile}" >&2
    cat "${logfile}" >&2
    exit 1
  fi
  if grep -qE '(FAILURES!|Skipped|Risky|incomplete)' "${logfile}"; then
    echo "Unexpected PHPUnit outcome in ${logfile}" >&2
    cat "${logfile}" >&2
    exit 1
  fi
  local version
  version="$("${DOCKER[@]}" run --rm --entrypoint php "${IMAGE}" -r 'echo PHP_VERSION;')"
  if [ "${version}" != "5.6.40" ]; then
    echo "Unexpected PHP version: ${version}" >&2
    exit 1
  fi
}

run_phpunit "/src/phpunit.xml" "${LOG}"
assert_clean_log "${LOG}"

SHUFFLED="$("${DOCKER[@]}" run --rm --entrypoint php \
  -v "${ROOT}:/src" -w /src \
  "${IMAGE}" \
  -d error_reporting=-1 \
  /src/tests/shuffle-run.php)"
run_phpunit "${SHUFFLED}" "${SHUFFLE_LOG}"
assert_clean_log "${SHUFFLE_LOG}"
rm -f "${ROOT}"/.phpunit-shuffled-*.xml

echo "SUITE_OK"
echo "LOG=${LOG}"
echo "SHUFFLE_LOG=${SHUFFLE_LOG}"
tail -n 1 "${LOG}"
tail -n 1 "${SHUFFLE_LOG}"
