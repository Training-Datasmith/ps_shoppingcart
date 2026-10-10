#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TARGET="${ROOT}/ps_shoppingcart.php"
BACKUP="$(mktemp)"
cp "${TARGET}" "${BACKUP}"

restore() {
  cp "${BACKUP}" "${TARGET}"
}

trap restore EXIT
trap 'restore; exit 130' INT
trap 'restore; exit 143' TERM

HASH_BEFORE="$(md5sum "${TARGET}" | awk '{print $1}')"
sed -i "s/if (!in_array(\$ajax, \[0, 1, '0', '1'\], true))/if (\$ajax != 0 \&\& \$ajax != 1)/" "${TARGET}"
HASH_AFTER="$(md5sum "${TARGET}" | awk '{print $1}')"
if [ "${HASH_BEFORE}" = "${HASH_AFTER}" ]; then
  echo "Mutation proof failed: sed made no substitution" >&2
  exit 1
fi

IMAGE="php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143"
PHAR="$(bash "${ROOT}/tests/verify-phpunit.sh")"
DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

LOG="$(mktemp)"
set +e
"${DOCKER[@]}" run --rm --entrypoint php \
  -v "${ROOT}:/src" -w /src \
  -v "${PHAR}:/opt/phpunit-4.8.36.phar:ro" \
  "${IMAGE}" \
  -d error_reporting=-1 \
  /opt/phpunit-4.8.36.phar \
  --configuration /src/phpunit.xml \
  --filter testSubmitGarbageShowsErrorAndDoesNotSave \
  >"${LOG}" 2>&1
STATUS=$?
set -e

if [ "${STATUS}" -eq 0 ]; then
  echo "Mutation proof failed: test still passed" >&2
  cat "${LOG}" >&2
  exit 1
fi

if grep -q 'Parse error:' "${LOG}"; then
  echo "Mutation proof failed: parse error" >&2
  exit 1
fi

if ! grep -q 'Tests: 1,' "${LOG}"; then
  echo "Mutation proof failed: expected exactly one test run" >&2
  cat "${LOG}" >&2
  exit 1
fi

if ! grep -q 'GetContentTest::testSubmitGarbageShowsErrorAndDoesNotSave' "${LOG}"; then
  echo "Mutation proof failed: expected test name in output" >&2
  cat "${LOG}" >&2
  exit 1
fi

if ! grep -qE '(Failures: 1|Errors: 1|FAILURES!|ERRORS!)' "${LOG}"; then
  echo "Mutation proof failed: expected named test failure or error" >&2
  cat "${LOG}" >&2
  exit 1
fi

echo "MUTATION_PROOF_OK"
