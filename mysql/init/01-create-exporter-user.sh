#!/bin/sh
# MySQL runs this during first initialization only. Keep the exporter separate from the app account.
set -eu

: "${MYSQL_EXPORTER_PASSWORD:?MYSQL_EXPORTER_PASSWORD must be set in the mysql service environment}"

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY '${MYSQL_EXPORTER_PASSWORD}';
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter'@'%';
FLUSH PRIVILEGES;
SQL
