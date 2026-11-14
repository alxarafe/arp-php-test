#!/bin/sh
set -eu
# The official MariaDB entrypoint invokes this script only on first DB initialization.
# The app account gets a distinct database for destructive integration tests.
mariadb --protocol=socket -u root -p"$MARIADB_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS arp_php_test_integration CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
GRANT ALL PRIVILEGES ON arp_php_test_integration.* TO '$MARIADB_USER'@'%';
SQL
