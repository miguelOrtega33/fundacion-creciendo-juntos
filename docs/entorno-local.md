# Entorno local

El entorno se ejecuta con Docker Compose y contiene:

- `app`: PHP-FPM 8.3 con Composer y Laravel.
- `nginx`: Nginx 1.27, publicado en `localhost:8080`.
- `mysql`: MySQL 8.4, con volumen persistente local.
- `mailpit`: SMTP en `localhost:1025` e interfaz en `localhost:8025`.
- `worker`: procesa la cola después de comprobar la clave de aplicación y las tablas requeridas.

En Windows se requiere Docker Desktop; en Linux, Docker Engine y Compose; en macOS, Docker Desktop.

```bash
cp .env.example .env
docker compose build app worker
docker compose run --rm --no-deps --user root app php artisan key:generate
docker compose up -d mysql mailpit
docker compose run --rm app php artisan migrate --force
docker compose up -d app nginx worker
docker compose exec app composer test
```

El orden es obligatorio en una instalación nueva: primero se genera `APP_KEY`, luego se ejecutan las migraciones y finalmente se inicia el worker. Como defensa adicional, el entrypoint del worker espera una clave no vacía y las tablas `jobs` y `failed_jobs` antes de consumir la cola.

La migración `phase_zero_checks` solo verifica la conexión y el ciclo de migraciones. No crea datos de negocio.

Las validaciones locales se ejecutan con:

```bash
docker compose exec app composer validate --strict
docker compose exec app composer audit --no-interaction
docker compose exec app composer run analyse
docker compose exec app composer run lint
docker compose exec app composer test
```

