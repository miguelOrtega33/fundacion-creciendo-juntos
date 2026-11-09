# Evidencia técnica de Fase 0

Fecha de ejecución: 2026-11-09.

Entorno comprobado:

- Docker Desktop 4.92.0.
- Docker Engine 29.8.0.
- Docker Compose 5.5.1.
- PHP 8.3.36 en el contenedor `app`.
- Laravel 13.40.0 fijado en `composer.lock`.
- MySQL 8.4, Nginx 1.27 y Mailpit 1.21.8 iniciados.

## Resultados

| Verificación | Resultado real |
|---|---|
| `docker compose config --quiet` | Correcto, código de salida 0 |
| `composer validate --strict` | `composer.json` válido |
| `composer audit --no-interaction` | Sin avisos de vulnerabilidades |
| `composer run analyse` | Sintaxis PHP válida |
| `composer run lint` | 22 archivos conformes |
| `php artisan route:list` | 4 rutas registradas; incluye `GET /health` |
| `php artisan migrate:status` | `2026_11_09_000000_create_phase_zero_checks_table` ejecutada, lote 1 |
| `composer test` | PHPUnit 12.5.43: 1 prueba, 2 aserciones, correcto |
| `GET http://127.0.0.1:8080/health` | HTTP 200, `application/json`, cuerpo `{"status":"ok","service":"fundacion-creciendo-juntos"}` |

La ejecución de GitHub Actions queda pendiente hasta abrir o actualizar un Pull Request. Esta evidencia no declara que el workflow remoto haya pasado.
