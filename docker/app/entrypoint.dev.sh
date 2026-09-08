#!/bin/bash

set -e

export APP_ENV=local
export APP_DEBUG=true

wait_for_postgres() {
    echo "Aguardando PostgreSQL em ${DB_HOST}:${DB_PORT}..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        echo "Tentativa $attempt de $max_attempts..."

        if php -r "new PDO(
            'pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
            '${DB_USERNAME}',
            '${DB_PASSWORD}'
        );" 2>/dev/null; then
            echo "✅ PostgreSQL está disponível!"
            return 0
        fi

        echo "⏳ PostgreSQL ainda não está pronto. Aguardando..."

        sleep 2
        attempt=$((attempt + 1))
    done

    echo "❌ Não foi possível conectar ao PostgreSQL após $max_attempts tentativas."

    return 1
}

wait_for_postgres

echo "================================="
echo "Configuração do banco:"
echo "DB_CONNECTION=${DB_CONNECTION}"
echo "DB_HOST=${DB_HOST}"
echo "DB_PORT=${DB_PORT}"
echo "DB_DATABASE=${DB_DATABASE}"
echo "DB_USERNAME=${DB_USERNAME}"
echo "================================="

echo "Limpando cache do Laravel..."

php artisan optimize:clear

echo "Verificando banco..."

php artisan tinker --execute="
echo 'Connection: ' . DB::connection()->getName() . PHP_EOL;
echo 'Database: ' . DB::connection()->getDatabaseName() . PHP_EOL;
echo 'Users: ' . \App\Models\User::count() . PHP_EOL;
"

echo "Executando migrações..."

php artisan migrate --force

echo "Iniciando servidor Laravel..."

php artisan serve \
    --host=0.0.0.0 \
    --port="${PORT:-8001}"