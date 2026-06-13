#!/usr/bin/env bash
# Installs the WordPress test scaffolding (core + PHPUnit test library) so the
# plugin's PHPUnit suite can run without wp-env. Based on the wp-cli scaffold.
#
# Usage: install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-db-create]

set -euo pipefail

DB_NAME=${1-wordpress_test}
DB_USER=${2-root}
DB_PASS=${3-root}
DB_HOST=${4-127.0.0.1}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}

download() {
	if command -v curl >/dev/null 2>&1; then
		curl -s "$1" > "$2"
	else
		wget -nv -O "$2" "$1"
	fi
}

# Resolve the test-library SVN tag/branch for the requested version.
if [[ "$WP_VERSION" == "latest" ]]; then
	WP_TESTS_TAG="trunk"
else
	WP_TESTS_TAG="tags/${WP_VERSION}"
fi

install_wp() {
	mkdir -p "$WP_CORE_DIR"
	if [[ "$WP_VERSION" == "latest" ]]; then
		local archive="https://wordpress.org/latest.tar.gz"
	else
		local archive="https://wordpress.org/wordpress-${WP_VERSION}.tar.gz"
	fi
	download "$archive" /tmp/wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"
}

install_test_suite() {
	mkdir -p "$WP_TESTS_DIR"
	svn export --quiet --force "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn export --quiet --force "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/" "$WP_TESTS_DIR/data"

	download "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"
	local config="$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s:dirname( __FILE__ ) . '/src/':'${WP_CORE_DIR}/':" "$config"
	sed -i "s/youremptytestdbnamehere/${DB_NAME}/" "$config"
	sed -i "s/yourusernamehere/${DB_USER}/" "$config"
	sed -i "s/yourpasswordhere/${DB_PASS}/" "$config"
	sed -i "s|localhost|${DB_HOST}|" "$config"
}

create_db() {
	if [[ "$SKIP_DB_CREATE" == "true" ]]; then
		return 0
	fi
	mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST" --protocol=tcp || true
}

install_wp
install_test_suite
create_db

echo "WP test suite installed (WP ${WP_VERSION}) in ${WP_CORE_DIR} / ${WP_TESTS_DIR}"
