# Fundación Creciendo Juntos

Plataforma ficticia de publicación y gestión editorial de noticias, creada como proyecto de portafolio profesional.

## Estado

Fundación documental. La Fase 0 fue autorizada el 2026-11-09 y se encuentra en preparación; no contiene funcionalidades de noticias.

## Stack previsto

PHP, MySQL, HTML, CSS, JavaScript, Docker Compose, PHPUnit y herramientas de pruebas de navegador y accesibilidad.

## Inicio local previsto

1. Copiar `.env.example` a `.env` y cambiar los valores locales.
2. Levantar los servicios con `docker compose up -d`.
3. Inicializar la base de datos mediante el procedimiento documentado en `docs/entorno-local.md`.
4. Ejecutar las pruebas indicadas en `docs/pruebas/estrategia.md`.

Los comandos definitivos y la versión exacta del framework quedan por confirmar antes de la implementación.

## Estructura

- `docs/`: arquitectura, ADR, pruebas, evidencias y trazabilidad.
- `database/`: migraciones y datos ficticios de demostración.
- `tests/`: pruebas organizadas por nivel.
- `.github/`: plantillas y automatización futura.

## Seguridad

No usar credenciales reales, datos personales ni contenido protegido. Las reglas obligatorias están en `AGENTS.md`.

## Licencia

Por confirmar.

