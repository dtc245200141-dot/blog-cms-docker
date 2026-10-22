#!/bin/sh
# Bootstrap WordPress core into the persistent volume, install the supplied local theme,
# and then delegate normal configuration/startup to the official WordPress entrypoint.
set -eu

mkdir -p /var/log/wordpress
chown www-data:www-data /var/log/wordpress

# The named wordpress_data volume starts empty on a fresh installation.
# Copy core first so the official entrypoint detects a populated WordPress directory.
if [ ! -f /var/www/html/wp-includes/version.php ]; then
    cp -a /usr/src/wordpress/. /var/www/html/
fi

# Copy the project theme into the persistent WordPress volume. This avoids mounting a
# read-only subdirectory inside /var/www/html, which can conflict with core ownership setup.
if [ -d /opt/blog-cms-theme ]; then
    mkdir -p /var/www/html/wp-content/themes/blog-cms-theme
    cp -R /opt/blog-cms-theme/. /var/www/html/wp-content/themes/blog-cms-theme/
    chown -R www-data:www-data /var/www/html/wp-content/themes/blog-cms-theme
fi

# A small must-use plugin selects the supplied theme once WordPress has been installed.
if [ -f /opt/blog-cms-auto-theme.php ]; then
    mkdir -p /var/www/html/wp-content/mu-plugins
    cp /opt/blog-cms-auto-theme.php /var/www/html/wp-content/mu-plugins/blog-cms-auto-theme.php
    chown www-data:www-data /var/www/html/wp-content/mu-plugins/blog-cms-auto-theme.php
fi

exec /usr/local/bin/docker-entrypoint.sh apache2-foreground
