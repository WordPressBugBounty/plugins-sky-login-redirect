#!/usr/bin/env bash

set -euo pipefail

plugin_root="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
output_path="${1:-"${plugin_root}/sky-login-redirect.zip"}"
staging_root="$(mktemp -d)"
staging_plugin="${staging_root}/sky-login-redirect"

cleanup() {
	rm -rf "${staging_root}"
}
trap cleanup EXIT

mkdir -p "${staging_plugin}"
rsync -a \
	--exclude='.git/' \
	--exclude='.phpunit.cache/' \
	--exclude='.phpunit.result.cache' \
	--exclude='node_modules/' \
	--exclude='sky-login-redirect.zip' \
	"${plugin_root}/" "${staging_plugin}/"

composer install \
	--working-dir="${staging_plugin}" \
	--no-dev \
	--prefer-dist \
	--no-interaction \
	--optimize-autoloader

wp dist-archive "${staging_plugin}" "${output_path}" --format=zip --force

printf 'Release archive created: %s\n' "${output_path}"
