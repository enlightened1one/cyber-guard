#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html
php bin/migrate.php
php bin/seed.php
exec apache2-foreground
