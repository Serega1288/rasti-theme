#!/bin/sh
set -eu

ensure_ai1wm_permissions() {
  mkdir -p /var/www/html/wp-content/ai1wm-backups

  if [ -d /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage ]; then
    chown -R 33:33 /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage
    find /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage -type d -exec chmod 777 {} \;
    find /var/www/html/wp-content/plugins/all-in-one-wp-migration/storage -type f -exec chmod 666 {} \;
  fi

  chown -R 33:33 /var/www/html/wp-content/ai1wm-backups
  find /var/www/html/wp-content/ai1wm-backups -type d -exec chmod 777 {} \;
  find /var/www/html/wp-content/ai1wm-backups -type f -exec chmod 666 {} \;
}

max_attempts=60
attempt=1

until [ -f /var/www/html/index.php ] && [ -f /var/www/html/wp-config.php ]; do
  if [ "$attempt" -ge "$max_attempts" ]; then
    echo "WordPress core files are not ready yet. Retrying via container restart."
    exit 1
  fi

  echo "Waiting for WordPress core files... ($attempt/$max_attempts)"
  attempt=$((attempt + 1))
  sleep 10
done

mkdir -p /var/www/html/wp-content/upgrade
mkdir -p /var/www/html/wp-content/plugins
mkdir -p /var/www/html/wp-content/uploads
mkdir -p /var/www/html/wp-content/ai1wm-backups
chown -R 33:33 /var/www/html/wp-content/plugins
chown -R 33:33 /var/www/html/wp-content/upgrade
chown -R 33:33 /var/www/html/wp-content/uploads
chown -R 33:33 /var/www/html/wp-content/ai1wm-backups
ensure_ai1wm_permissions

attempt=1

until wp core is-installed --path=/var/www/html --allow-root >/dev/null 2>&1; do
  if [ "$attempt" -ge "$max_attempts" ]; then
    echo "WordPress is not installed yet. Retrying via container restart."
    exit 1
  fi

  echo "Waiting for WordPress installation... ($attempt/$max_attempts)"
  attempt=$((attempt + 1))
  sleep 10
done

plugins=$(echo "${WP_AUTO_PLUGINS:-}" | tr ',' ' ')

for plugin in $plugins; do
  if wp plugin is-installed "$plugin" --path=/var/www/html --allow-root >/dev/null 2>&1; then
    echo "Plugin already installed: $plugin"
  else
    echo "Installing plugin: $plugin"
    if wp plugin install "$plugin" --activate --path=/var/www/html --allow-root; then
      chown -R 33:33 "/var/www/html/wp-content/plugins/$plugin" || true
      if [ "$plugin" = "all-in-one-wp-migration" ]; then
        ensure_ai1wm_permissions
      fi
      echo "Installed plugin: $plugin"
    else
      echo "Skipping plugin that could not be installed automatically: $plugin"
    fi
  fi
done

for plugin_dir in /var/www/local-plugins/*; do
  if [ ! -d "$plugin_dir" ]; then
    continue
  fi

  plugin=$(basename "$plugin_dir")

  if [ "$plugin" = ".gitkeep" ]; then
    continue
  fi

  if wp plugin is-installed "$plugin" --path=/var/www/html --allow-root >/dev/null 2>&1; then
    echo "Local plugin already installed: $plugin"
    continue
  fi

  echo "Installing local plugin directory: $plugin"
  rm -rf "/var/www/html/wp-content/plugins/$plugin"
  cp -R "$plugin_dir" "/var/www/html/wp-content/plugins/$plugin"
  chown -R 33:33 "/var/www/html/wp-content/plugins/$plugin"
  if [ "$plugin" = "all-in-one-wp-migration" ]; then
    ensure_ai1wm_permissions
  fi
  if wp plugin activate "$plugin" --path=/var/www/html --allow-root; then
    echo "Activated local plugin: $plugin"
  else
    echo "Copied local plugin but activation failed: $plugin"
  fi
done

for plugin_zip in /var/www/local-plugins/*.zip; do
  if [ ! -f "$plugin_zip" ]; then
    continue
  fi

  plugin=$(basename "$plugin_zip" .zip)

  if wp plugin is-installed "$plugin" --path=/var/www/html --allow-root >/dev/null 2>&1; then
    echo "Local zip plugin already installed: $plugin"
    continue
  fi

  echo "Installing local plugin zip: $(basename "$plugin_zip")"
  if wp plugin install "$plugin_zip" --activate --path=/var/www/html --allow-root; then
    chown -R 33:33 "/var/www/html/wp-content/plugins/$plugin" || true
    if [ "$plugin" = "all-in-one-wp-migration" ]; then
      ensure_ai1wm_permissions
    fi
    echo "Installed local plugin: $plugin"
  else
    echo "Skipping local plugin that could not be installed: $plugin"
  fi
done

echo "Plugin installation completed."
