#!/usr/bin/env bash
set -euo pipefail

WORKDIR="$(mktemp -d)"
PHAR="${WORKDIR}/phpunit-4.8.36.phar"
ASC="${WORKDIR}/phpunit-4.8.36.phar.asc"
GPG_ASC="${WORKDIR}/gpg.asc"
EXPECTED_SHA="ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e"
EXPECTED_FPR="D8406D0D82947747293778314AA394086372C20A"

curl -fsSL -o "${PHAR}" https://phar.phpunit.de/phpunit-4.8.36.phar
curl -fsSL -o "${ASC}" https://phar.phpunit.de/phpunit-4.8.36.phar.asc
curl -fsSL -o "${GPG_ASC}" https://sebastian-bergmann.de/gpg.asc

export GNUPGHOME="$(mktemp -d)"
chmod 700 "${GNUPGHOME}"
gpg --batch --import "${GPG_ASC}" >/dev/null

STATUS_FILE="${WORKDIR}/gpg-status.txt"
gpg --batch --status-fd 3 --verify "${ASC}" "${PHAR}" 3>"${STATUS_FILE}" 2>/dev/null

if ! grep -q "^\[GNUPG:\] VALIDSIG ${EXPECTED_FPR} " "${STATUS_FILE}"; then
  echo "PHPUnit PHAR signature verification failed" >&2
  exit 1
fi

ACTUAL_SHA="$(sha256sum "${PHAR}" | awk '{print $1}')"
if [ "${ACTUAL_SHA}" != "${EXPECTED_SHA}" ]; then
  echo "PHPUnit PHAR SHA-256 mismatch" >&2
  exit 1
fi

echo "${PHAR}"
