# Entorno local

El entorno se ejecuta con Docker Compose y contiene:

- `app`: PHP-FPM 8.3 con Composer y Laravel.
- `nginx`: Nginx 1.27, publicado en `localhost:8080`.
- `mysql`: MySQL 8.4, con volumen persistente local.
- `mailpit`: SMTP en `localhost:1025` e interfaz en `localhost:8025`.

En Windows se requiere Docker Desktop; en Linux, Docker Engine y Compose; en macOS, Docker Desktop.

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app composer test
```

La migración `phase_zero_checks` solo verifica la conexión y el ciclo de migraciones. No crea datos de negocio.

Las validaciones locales se ejecutan con:

```bash
docker compose exec app composer validate --strict
docker compose exec app composer audit --no-interaction
docker compose exec app composer run analyse
docker compose exec app composer run lint
docker compose exec app composer test
```

