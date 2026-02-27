#!/usr/bin/env sh
set -eu

APP_DIR="/var/www/html"
cd "$APP_DIR"

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

php -r '
$host = getenv("DB_HOST") ?: "postgres";
$port = getenv("DB_PORT") ?: "5432";
$db = getenv("DB_NAME") ?: "loans";
$user = getenv("DB_USER") ?: "user";
$pass = getenv("DB_PASSWORD") ?: "password";
for ($i = 0; $i < 60; $i++) {
    try {
        new PDO("pgsql:host={$host};port={$port};dbname={$db}", $user, $pass);
        exit(0);
    } catch (Throwable $e) {
        usleep(500000);
    }
}
fwrite(STDERR, "Database is not available after 30 seconds.\n");
exit(1);
';

php yii migrate/up --interactive=0

exec php-fpm
