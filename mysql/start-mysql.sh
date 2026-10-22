#!/bin/sh
# Run as root before the official MySQL entrypoint. Give the mysql account a dedicated writable log volume.
set -eu
mkdir -p /var/log/mysql
chown mysql:mysql /var/log/mysql
exec /usr/local/bin/docker-entrypoint.sh "$@" --log-error=/var/log/mysql/error.log
