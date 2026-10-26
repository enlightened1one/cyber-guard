<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';

Database::runMigrations();

echo "Database migration completed successfully.\n";
