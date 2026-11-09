# ADR-0004: MySQL 8.4 y migraciones versionadas

- Estado: Aprobado
- Fecha: 2026-11-09

## Decisión

El entorno local usará MySQL 8.4. El esquema se modificará mediante migraciones Laravel versionadas y ejecutables de forma idempotente con `php artisan migrate`.

## Motivo

Las migraciones mantienen el esquema junto al código, permiten revisar cambios por Pull Request y hacen reproducible la creación de ambientes.

## Alcance

La Fase 0 solo incluye `phase_zero_checks` como comprobación técnica. Las tablas de noticias, usuarios, permisos y auditoría se definirán en fases posteriores.
