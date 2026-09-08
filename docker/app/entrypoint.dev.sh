#!/bin/bash

echo "Removendo arquivo .env existente..."
rm -f .env

echo "Criando arquivo .env..."
cat > .env << EOF
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
EOF

wait_for_postgres() {
    echo "Aguardando PostgreSQL em ${DB_HOST}:${DB_PORT}..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        echo "Tentativa $attempt de $max_attempts..."

        if php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; then
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

if [ $? -ne 0 ]; then
    exit 1
fi

echo "Gerando application key..."
php artisan key:generate --no-interaction

echo "Executando migrações..."
php artisan migrate --force

echo "Iniciando servidor Laravel..."
php artisan serve \
    --host=0.0.0.0 \
    --port="${PORT:-8001}"