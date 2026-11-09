# ADR-0005: Docker Compose y servicios locales

- Estado: Aprobado
- Fecha: 2026-11-09

## Decisión

Docker Compose orquestará cuatro servicios: PHP-FPM 8.3 para Laravel, Nginx 1.27 como servidor web, MySQL 8.4 y Mailpit para correo local.

## Motivo

El entorno reduce diferencias entre Windows/Docker Desktop, Linux y macOS, y evita instalar PHP, Composer, Nginx o MySQL en el equipo anfitrión.

## Puertos locales

- Aplicación: `8080`.
- MySQL: `3306`.
- Mailpit SMTP: `1025`.
- Mailpit web: `8025`.

No se incluyen credenciales reales ni servicios productivos.
