#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TARGET="${ROOT}/ps_shoppingcart.php"
BACKUP="$(mktemp)"
cp "${TARGET}" "${BACKUP}"
trap 'cp "${BACKUP}" "${TARGET}"' EXIT INT TERM

sed -i "s/if (!in_array(\$ajax, array(0, 1, '0', '1'), true))/if (\$ajax != 0 \&\& \$ajax != 1)/" "${TARGET}"

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

echo "MUTATION_PROOF_OK"
