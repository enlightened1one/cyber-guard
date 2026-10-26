<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once APP_ROOT . '/database/seed.php';

Database::runMigrations();
runSeed(Database::getConnection());

echo "Seed process completed successfully.\n";
