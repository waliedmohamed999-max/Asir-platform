#!/usr/bin/env sh
set -eu

if command -v composer >/dev/null 2>&1; then
  COMPOSER_BIN="composer"
else
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet --install-dir=. --filename=composer
  rm -f composer-setup.php
  COMPOSER_BIN="php ./composer"
fi

COMPOSER_ALLOW_SUPERUSER=1 $COMPOSER_BIN install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm install
npm run build
