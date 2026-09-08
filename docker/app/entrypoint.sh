#!/bin/bash

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

if [ ! -f .env ]; then
    echo "Criando arquivo .env..."

    cat > .env << 'EOF'
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
fi

sed -i "s/DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
sed -i "s/DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env

if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d= -f2)" ]; then
    echo "Gerando application key..."

    php artisan key:generate --no-interaction
fi

echo "Executando migrações..."

php artisan migrate --force

echo "Iniciando servidor Laravel..."

php artisan serve --host=0.0.0.0 --port=8001